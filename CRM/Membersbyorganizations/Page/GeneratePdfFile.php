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
      $members = [];

      /* Get the membership id and display name of the employee. */
      $member_sql = "SELECT
      contact_a.display_name AS `display_name`,
      civicrm_membership.id AS `membership_id`,
      civicrm_membership.owner_membership_id AS `owner_membership_id`
      FROM civicrm_contact contact_a
      LEFT JOIN civicrm_membership ON civicrm_membership.contact_id = contact_a.id
      LEFT JOIN civicrm_contribution_recur ccr ON (civicrm_membership.contribution_recur_id = ccr.id)
      INNER JOIN civicrm_membership_status ON civicrm_membership.status_id = civicrm_membership_status.id
      INNER JOIN civicrm_membership_type ON civicrm_membership.membership_type_id = civicrm_membership_type.id
      WHERE (contact_a.display_name LIKE %1
      AND contact_a.contact_type IN ('Organization')
      AND civicrm_membership.status_id IN ('2')  -- Current
      AND civicrm_membership_status.is_current_member = 1
      AND civicrm_membership.is_test = 0)
      AND(1) AND (contact_a.is_deleted = 0)
      GROUP BY civicrm_membership.id;";

      $name = CRM_Utils_Type::escape($org_name, 'String');
      $params = [1 => ["%{$name}%%", 'String']];
      $dao = CRM_Core_DAO::executeQuery($member_sql, $params);

      while ($dao->fetch()) {
        /* Check the membership id of the employee. */
        $id = (!$dao->owner_membership_id) ? $dao->membership_id : $dao->owner_membership_id;
        if(empty($id)){
          continue;
        }

        /* Get the contact id of the employee. */
        $sql = "SELECT contact_id FROM `civicrm_membership` WHERE ( `civicrm_membership`.`id` = {$id} )";
        $inner_dao = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

        while ($inner_dao->fetch()) {
          /* Fetch contact details of the employee. */
          $member = civicrm_api3('Contact', 'getsingle', [
            'return' => ["display_name", "first_name", "last_name", "sort_name"],
            'id' => $inner_dao->contact_id,
          ]);

          $members[$member['sort_name']] = [
            'display_name' => $member['display_name'],
          ];
        }
      }

      $rel_contact = civicrm_api3('Relationship', 'get', [
        'sequential' => 1,
        'return' => ["contact_id_a.display_name", "contact_id_a.sort_name"],
        'contact_id_b' => $org_id,
        'options' => ['limit' => ""]
      ]);

      if ($rel_contact['count']) {
        foreach ($rel_contact['values'] as $con) {
          $members[$con['contact_id_a.sort_name']] = [
            'display_name' => $con['contact_id_a.display_name'],
          ];
        }
      }

      /* Sorting the array by key `sort_name`. */
      ksort($members,SORT_REGULAR);

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
              <th scope='col'>Name</th>
            </tr>
          </thead>
            <tbody>";
        foreach ($members as $member) {
          $html .= "<tr scope='row'>
              <td align='center' >". $member['display_name'] ."</td>
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
