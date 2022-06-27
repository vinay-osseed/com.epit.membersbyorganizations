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

      /* Getting the membership type id of the organization. */
      $membership_type = civicrm_api3('MembershipType', 'get', [
        'sequential' => 1,
        'return' => ["id"],
        'member_of_contact_id' => $org_id,
        'relationship_type_id' => 5, // Employee of
        'relationship_direction' => "a_b",
        'is_active' => 1,
      ]);

      /* Checking if the membership type is found or not. */
      if (!$membership_type['count'] || $membership_type['is_error']) {
        CRM_Core_Session::setStatus(" ", ts('Membership Type Not Found.'), "warning");
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/list-org', NULL, FALSE, NULL, FALSE, TRUE));
      }

      /* Get the list of all the employees of the organization. */
      $contacts = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'return' => ["display_name"],
        'contact_type' => "Individual",
        'api.Membership.get' => [
            'status_id' => 2, // Current
            'relationship_name' => "Employee of",
            'membership_type_id' => $membership_type['id']
        ],
        'api.Relationship.get' => [
          'sequential' => 1,
          'relationship_type_id' => 5 // Employee of
        ],
        'options' => ['sort' => "last_name", 'limit' => ""],
      ]);

      $members = [];
      if ($contacts['count']) {
        foreach ($contacts['values'] as $contact) {
            if ($contact['api.Membership.get']['count']) {
              /* Check if the contact is an employee of the organization. */
              foreach ($contact['api.Relationship.get']['values'] as $con) {
                if ($con['contact_id_b'] == $org_id) {
                  $members[] = [
                      'display_name' => $contact['display_name'],
                  ];
                }
              }
            }
          }
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
