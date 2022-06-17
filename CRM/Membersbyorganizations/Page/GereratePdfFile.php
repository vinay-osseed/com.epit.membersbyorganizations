<?php
use CRM_Membersbyorganizations_ExtensionUtil as E;

class CRM_Membersbyorganizations_Page_GereratePdfFile extends CRM_Core_Page{

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

      /* Get the organization name and the list of employees of that organization. */
      $contacts = civicrm_api3('Relationship', 'get', [
        'sequential' => 1,
        'return' => ["contact_id_a.display_name", "contact_id_b.display_name"],
        'contact_id_b' => $org_id,
        'contact_id_a.contact_type' => "Individual",
        'options' => ['sort' => "contact_id_a.last_name"],
      ]);

      /* If there are no employees found for the organization, then it will display a warning message
      and redirect the user to the list of organization's page. */
      if (!$contacts['count']) {
        CRM_Core_Session::setStatus(" ", ts('No Employees Found.'), "warning");
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/list-org', NULL, FALSE, NULL, FALSE, TRUE));
      }
      $org_name = $contacts['values'][0]['contact_id_b.display_name'];

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
              foreach ($contacts['values'] as $contact) {
                $html .= "<tr scope='row'>
                  <td align='center' >". $contact['contact_id_a.display_name'] ."</td>
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
