<?php
use CRM_Membersbyorganizations_ExtensionUtil as E;

class CRM_Membersbyorganizations_Page_GereratePdfFile extends CRM_Core_Page{

  public function run(){
    CRM_Utils_System::setTitle(E::ts('Download PDF'));

    if (isset($_GET['org_id'])) {
      /* Getting the current date in the format of `dd-mm-yyyy` */
      $today = date('d-m-Y');

      /* Get the organization name and the list of employees of that organization. */
      $org_name = civicrm_api3('Contact', 'getsingle', [
        'return' => ["display_name"],
        'id' => $_GET['org_id'],
        'contact_type' => "Organization",
      ])["display_name"];

      $contact_ids = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'return' => ["display_name"],
        'employer_id' => $_GET['org_id'],
        'options' => ['sort' => "last_name"],
      ]);

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
      foreach ($contact_ids['values'] as $contact) {
        $html .= "<tr scope='row'>
          <td align='center' >". $contact['display_name'] ."</td>
        </tr>";
      }
      $html .= "</tbody></table>";

      /* Generate the pdf file. */
      $pdf_filename = "Employees.pdf";
      $pdf_file_path = 'public://' . $pdf_filename;
      file_put_contents($pdf_file_path, CRM_Utils_PDF_Utils::html2pdf($html, $pdf_filename, true));

      /* Assigning the values to the template file. */
      $this->assign('org_id', $_GET['org_id']);
      // $this->assign('pdf_file', Civi::paths()->getUrl($pdf_file_path));
    }
    parent::run();
  }
}
