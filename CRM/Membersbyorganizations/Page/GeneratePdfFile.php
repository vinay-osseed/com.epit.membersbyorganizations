<?php
use CRM_Membersbyorganizations_ExtensionUtil as E;

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

      /* Get the organization name. */
      $org = civicrm_api3('Contact', 'getsingle', [
        'return' => ["display_name"],
        'id' => $org_id,
        'contact_type' => "Organization",
      ]);
      $org_name = $org['display_name'];
      $name = CRM_Utils_Type::escape($org_name, 'String');
      $members = [];

      /* Get a list of contacts who are employees of the organization. */
      $rel_contacts = \Civi\Api4\Contact::get()
        ->addSelect('display_name', 'sort_name', 'first_name', 'last_name', 'membership.id')
        ->addJoin('Membership AS membership', 'LEFT', ['membership.contact_id', '=', 'id'])
        ->addJoin('ContributionRecur AS contribution_recur', 'LEFT', ['membership.contribution_recur_id', '=', 'contribution_recur.id'])
        ->addJoin('MembershipStatus AS membership_status', 'LEFT', ['membership.status_id', '=', 'membership_status.id'])
        ->addJoin('Relationship AS relationship', 'LEFT', ['relationship.contact_id_a', '=', 'id'])
        ->addJoin('Contact AS contact', 'LEFT', ['contact.id', '=', 'relationship.contact_id_b'])
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
        /* If the first and last name is empty, then the display name is assigned as first name. */
        if (empty($contact['first_name']) && empty($contact['last_name'])) {
          $contact['first_name'] = $contact['display_name'];
        }
        $members[$contact['sort_name']] = [
          'first_name' => $contact['first_name'],
          'last_name' => $contact['last_name'],
          'membership_id' => isset($contact['membership.id']) ? $contact['membership.id'] : 'None',
        ];
      }

      /* If there are no employees found for the organization, then it will display a warning message
      and redirect the user to the list of organization's page. */
      if (empty($members)) {
        CRM_Core_Session::setStatus(" ", ts('No Employees Found.'), "warning");
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/list-org', NULL, FALSE, NULL, FALSE, TRUE));
      }

      /* Generate the html code for the pdf file. */
      $html = "<h1 align='center'>". $org_name ."</h1>
        <h4 align='right'>Date :- ". $today ."</h4>
        <h3 align='center'>Current Employees</h3>
        <table border='1' style='width: 100%;border-collapse: collapse;'>
          <thead>
            <tr>
              <th scope='col'>First Name</th>
              <th scope='col'>Last Name</th>
              <th scope='col'>Membership No</th>
            </tr>
          </thead>
            <tbody>";
        foreach ($members as $member) {
          $html .= "<tr scope='row'>
              <td align='center' >". $member['first_name'] ."</td>
              <td align='center' >". $member['last_name'] ."</td>
              <td align='center' >". $member['membership_id'] ."</td>
            </tr>";
        }
      $html .= "</tbody></table>";

      /* Generate the pdf file. */
      $pdf_filename = "Employees.pdf";
      $pdf_contents = CRM_Utils_PDF_Utils::html2pdf($html, $pdf_filename, true);

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
        'name' => $pdf_filename,
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