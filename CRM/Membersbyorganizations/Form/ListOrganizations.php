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

    $fromClause = "FROM civicrm_contact ";

    $whereClause = "WHERE civicrm_contact.contact_type = %1
      AND civicrm_contact.is_deleted = 0 ";

    $pager_params = [
      1 => [$param['contact_type'], 'String']
    ];

    /* If the user enters a name in the search box, the
    list will be filtered by that name. */
    if(isset($_GET['display_name']) && !empty($_GET['display_name'])){
      $param['display_name'] = ['LIKE' => '%' . $_GET['display_name'] . '%'];
      // $param['options']['limit'] = "";
      $whereClause .= "AND civicrm_contact.display_name LIKE \"%" . $_GET['display_name'] ."%\"";
    }

    /* If the user selects a tag from the dropdown, the
    list will be filtered by that tag. */
    if(isset($_GET['tag_id']) && !empty($_GET['tag_id'])){
      $param['tag'] = $_GET['tag_id'];
      // $param['options']['limit'] = "";
      // $whereClause .= "AND (?) = \"%" . $_GET['tag_id'] ."%\"";
    }

    $this->pager($fromClause, $whereClause, $pager_params);

    list($offset, $rowCount) = $this->_pager->getOffsetAndRowCount();
    if (!isset($param['options']['limit'])) {
      $param['options'] = ['limit' => $rowCount, 'offset' => $offset];
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

  /**
   * @param $fromClause
   * @param $whereClause
   * @param array $whereParams
   */
  public function pager($fromClause, $whereClause, $whereParams) {

    $params = [];
    $params['status'] = ts('Group') . ' %%StatusMessage%%';
    $params['csvString'] = NULL;
    $params['buttonTop'] = 'PagerTopButton';
    $params['buttonBottom'] = 'PagerBottomButton';
    $params['rowCount'] = $this->get(CRM_Utils_Pager::PAGE_ROWCOUNT);

    if (!$params['rowCount']) {
      $params['rowCount'] = Civi::settings()->get('default_pager_size');
    }

    $query = "SELECT count( civicrm_contact.id ) $fromClause $whereClause";
    $params['total'] = CRM_Core_DAO::singleValueQuery($query, $whereParams);

    $this->_pager = new CRM_Utils_Pager($params);
    $this->assign_by_ref('pager', $this->_pager);
  }

}
