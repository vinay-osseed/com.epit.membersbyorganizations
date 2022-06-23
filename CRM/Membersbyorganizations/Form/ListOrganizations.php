<?php

use CRM_Membersbyorganizations_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Membersbyorganizations_Form_ListOrganizations extends CRM_Core_Form {
  public function buildQuickForm() {

    /* It's adding a text field for name in the form. */
    $this->addElement('text', 'display_name', ts('Organization Name'), TRUE);

    /* Adding a dropdown list of tags to the form. */
    $tag = ['' => ts('- any tag -')] + CRM_Core_PseudoConstant::get('CRM_Core_DAO_EntityTag', 'tag_id', ['onlyActive' => FALSE]);
    $this->addElement('select', 'tag_id', ts('Tagged'), $tag, ['class' => 'crm-select2']);

    /* It's adding a submit button to the form. */
    $this->addButtons([['type' => 'submit', 'name' => E::ts('Search'), 'isDefault' => TRUE]]);

    parent::buildQuickForm();
  }

  /**
   * Called after form is successfully submitted
   */
  public function postProcess() {
    $values =$this->controller->exportValues($this->_name);
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/list-org', $values, FALSE, NULL, FALSE, TRUE));
    parent::postProcess();
  }

  /**
   * This function is called prior to building and submitting the form
   */
  public function preProcess() {
    CRM_Utils_System::setTitle(E::ts('List Organizations'));

    /* API Paramaters */
    $param = [
      'return' => ["display_name"],
      'contact_type' => "Organization",
    ];

    /* If the user enters a name in the search box, the
    list will be filtered by that name. */
    if(isset($_GET['display_name']) && !empty($_GET['display_name'])){
      $param['display_name'] = ['LIKE' => $_GET['display_name']];
    }

    /* If the user selects a tag from the dropdown, the
    list will be filtered by that tag. */
    if(isset($_GET['tag_id']) && !empty($_GET['tag_id'])){
      $param['tag'] = $_GET['tag_id'];
    }


    /* Get a list of all organizations. */
    $orgs = civicrm_api3('Contact', 'get', $param);

    /* If the count of organizations is 0, then it will display a warning message
    and redirect to the List Organizations page. */
    if(!$orgs['count']){
      CRM_Core_Session::setStatus(" ", ts('No Organization Found.'), "warning");
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/list-org', NULL, FALSE, NULL, FALSE, TRUE));
    }

    /* Assigning the value of API result to the template variable 'orgs'. */
    $this->assign('orgs', $orgs['values']);
  }

}
