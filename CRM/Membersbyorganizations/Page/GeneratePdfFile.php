<?php
use CRM_Membersbyorganizations_ExtensionUtil as E;
use Civi\Token\TokenProcessor;

class CRM_Membersbyorganizations_Page_GeneratePdfFile extends CRM_Core_Page{

  public function run(){
    CRM_Utils_System::setTitle(E::ts('Download PDF'));

    if (isset($_GET['org_id'])) {
      /* Get the current date, organization id, activity type id
      and logged contact id. */
      $today = date('d-m-Y');
      $org_id = $_GET['org_id'];
      $logged_contact_id = CRM_Core_Session::getLoggedInContactID();
      $activity_type = CRM_Core_PseudoConstant::getKey(
        'CRM_Activity_BAO_Activity',
        'activity_type_id',
        'Print PDF Letter'
      );

      /* Get the organization contact and the message template. */
      $org = civicrm_api3('Contact', 'getsingle', [
        'return' => ["display_name"],
        'id' => $org_id,
        'contact_type' => "Organization",
      ]);
      $msg_tpl = civicrm_api3('MessageTemplate', 'getsingle', [
        'msg_title' => "Employee List of Organization",
      ]);

      $org_name = $org['display_name'];
      $tpl_params = [
        'org_name' => $org_name,
        'style' => 'page-break-before: auto',
      ];
      $name = CRM_Utils_Type::escape($org_name, 'String');
      $members = [];

      $tokenProcessor = new TokenProcessor(\Civi::dispatcher(), [
        /* Give a unique identifier for this instance. */
        'controller' => __CLASS__,
        /* Enable or disable handling of Smarty notation. */
        'smarty' => FALSE,
        /* List any data fields that we plan to provide. */
        'schema' => ['contactId'],
      ]);

      /* Get a list of contacts who are employees of the organization. */
      $rel_contacts = \Civi\Api4\Contact::get()
        ->addSelect('display_name', 'sort_name', 'first_name', 'last_name', 'membership.id', 'membership.status_id', 'membership.membership_type_id:label', 'Member_Profile_administration.Membership_number')
        ->addJoin('Membership AS membership', 'LEFT', ['membership.contact_id', '=', 'id'])
        ->addJoin('ContributionRecur AS contribution_recur', 'LEFT', ['membership.contribution_recur_id', '=', 'contribution_recur.id'])
        ->addJoin('MembershipStatus AS membership_status', 'LEFT', ['membership.status_id', '=', 'membership_status.id'])
        ->addJoin('Relationship AS relationship', 'LEFT', ['relationship.contact_id_a', '=', 'id'])
        ->addJoin('Contact AS contact', 'LEFT', ['contact.id', '=', 'relationship.contact_id_b'])
        ->addJoin('EntityTag AS entity_tag', 'LEFT', ['entity_tag.entity_id', '=', 'relationship.contact_id_a'], ['entity_tag.entity_table', '=', "'civicrm_contact'"])
        ->addGroupBy('id')
        ->addWhere('contact.sort_name', 'LIKE', "%{$name}%")
        ->addWhere('relationship.is_active', '=', TRUE)
        ->addWhere('contact.is_deleted', '=', FALSE)
        ->addWhere('relationship.relationship_type_id', '=', 5) // Employee of
        ->addWhere('membership_status.is_current_member', '=', TRUE) // Current
        ->addWhere('membership.is_test', '=', FALSE)
        ->addWhere('is_deleted', '=', FALSE)
        ->addOrderBy('sort_name', 'ASC')
        ->execute();
      foreach ($rel_contacts as $contact) {
        /* Adding a row to the token processor. */
        $tokenProcessor->addRow(['contactId' => $contact['id']]);

        /**
         * This is a workaround to exclude the contacts who have the tag "Not for renewal".
         * @link https://docs.civicrm.org/user/en/latest/organising-your-data/groups-and-tags/#limitations-of-tags : 2nd point
         */
        $tags = civicrm_api3('Contact', 'getsingle', [
          'return' => ["tag"],
          'id' => $contact['id'],
          'contact_type' => "Individual",
        ])["tags"];
        if (in_array("Not for renewal", explode(",", $tags))) {
          continue;
        }

        /* If the first and last name is empty, then the display name is assigned as first name. */
        if (empty($contact['first_name']) && empty($contact['last_name'])) {
          $contact['first_name'] = $contact['display_name'];
        }
        $members[$contact['sort_name']] = [
          'first_name' => $contact['first_name'],
          'last_name' => $contact['last_name'],
          'membership_id' => isset($contact['membership.id']) ? $contact['membership.id'] : 'None',
          'membership_number' => isset($contact['Member_Profile_administration.Membership_number']) ? $contact['Member_Profile_administration.Membership_number'] : 'None',
          /* Checking if the contact has a membership status of 2 (current) and if so, it is displaying the
            membership type. If not, it is displaying "None". */
          'membership_type' => ($contact['membership.status_id'] == 2) ? $contact['membership.membership_type_id:label'] : 'None',
        ];
      }
      $tpl_params['members'] = $members;

      /* Get the token from template . */
      $pattern = "/{contact.*}/i";
      preg_match_all($pattern, $msg_tpl['msg_html'],$matches);
      $token = implode("",$matches[0]);

      /* Define the message template. */
      $tokenProcessor->addMessage('token_value','<td align="center">'.$token.'</td>', 'text/html');

      /* Evaluate any tokens which are referenced in the message. */
      $tokenProcessor->evaluate();
      foreach ($tokenProcessor->getRows() as $row) {
        $mem_no[] = $row->render('token_value');
      }

      /* Generate the pdf file. */
      $pdf_name = "Employees.pdf";

      /* Send the message template parameters. */
      $send_tpl_params = [
          'messageTemplateID' =>(int) $msg_tpl['id'],
          'tplParams' => $tpl_params,
          'tokenContext' => ['contactId' => $org_id, 'smarty' => TRUE],
          'PDFFilename' => $pdf_name,
      ];

      /* If there are no members of the organization, then the message template is not sent. */
      if (empty($tpl_params['members'])) {
        CRM_Core_Session::setStatus(" ", ts('No Employees Found.'), "warning");
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/list-org', NULL, FALSE, NULL, FALSE, TRUE));
      }

      /* Generate the html code for the pdf file. */
      list($sent, $subject, $message, $html) = CRM_Core_BAO_MessageTemplate::sendTemplate($send_tpl_params);

      /* This is a workaround to replace the token with the membership number. */
      foreach ($mem_no as $key => $value) {
        $html = str_replace('<td align="center" id="'.$key.'"></td>', $value, $html);
      }
      $pdf_contents = CRM_Utils_PDF_Utils::html2pdf($html, $pdf_name, true);

      /* Creating an activity and attaching the pdf file to that activity. */
      $activity = civicrm_api3('Activity', 'create', [
        'subject' => 'Download Employee PDF File',
        'source_contact_id' => $logged_contact_id,
        'activity_type_id' => $activity_type,
        'target_contact_id' => $org_id,
      ]);
      $attachment = civicrm_api3('Attachment', 'create', [
        'sequential' => 1,
        'entity_table' => 'civicrm_activity',
        'entity_id' => $activity['id'],
        'name' => $pdf_name,
        'mime_type' => 'application/pdf',
        'content' => $pdf_contents,
      ]);
      $pdf_url = $attachment['values'][0]['url'];

      /* Redirecting the user to download the pdf file. */
      CRM_Utils_System::redirect($pdf_url);
    }
    parent::run();
  }
}