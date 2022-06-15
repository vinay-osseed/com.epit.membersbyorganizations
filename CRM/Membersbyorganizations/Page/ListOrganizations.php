<?php
use CRM_Membersbyorganizations_ExtensionUtil as E;

class CRM_Membersbyorganizations_Page_ListOrganizations extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('List Organizations'));

    /* Get a list of all organizations. */
    $orgs = civicrm_api3('Contact', 'get', [
      'return' => ["display_name"],
      'contact_type' => "Organization",
    ]);
    $this->assign('orgs', $orgs['values']);

    parent::run();
  }

}
