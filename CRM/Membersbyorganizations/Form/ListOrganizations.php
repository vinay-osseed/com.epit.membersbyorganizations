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
    $this->addElement('text', 'display_name', ts('Name or Email'), TRUE);

    /* Adding a dropdown list of tags to the form. */
    $tag = ['' => ts('- any tag -')] + CRM_Core_PseudoConstant::get('CRM_Core_DAO_EntityTag', 'tag_id', ['onlyActive' => FALSE]);
    $this->addElement('select', 'tag_id', ts('With'), $tag, ['class' => 'crm-select2']);

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

    /* Default table to the query. */
    $fromClause = "FROM civicrm_contact cc ";

    /* Default condition to the query. */
    $whereClause = "WHERE cc.contact_type = %1  AND cc.is_deleted = 0 ";

    $pager_params = [
      1 => [$param['contact_type'], 'String']
    ];

    /* If the user enters a name in the search box, the
    list will be filtered by that name. */
    if(isset($_GET['display_name']) && !empty($_GET['display_name'])){

      /* It's escaping the value of the display_name parameter. */
      $value = CRM_Utils_Type::escape("{$_GET['display_name']}", 'String');
      $param['display_name'] = ['LIKE' => "$value"];

      /* It's adding a condition to the query. */
      $whereClause .= "AND cc.display_name LIKE \"%{$value}%\" ";
    }

    /* If the user selects a tag from the dropdown, the
    list will be filtered by that tag. */
    if(isset($_GET['tag_id']) && !empty($_GET['tag_id'])){
      $param['tag'] = $_GET['tag_id'];

      /* It's adding a join to the query. */
      $fromClause .= "LEFT JOIN civicrm_entity_tag cet
        ON ( cet.entity_id = cc.id  AND cet.entity_table = 'civicrm_contact')";

      $whereClause .= "AND cet.tag_id = {$_GET['tag_id']} ";
    }

    $this->pager($fromClause, $whereClause, $pager_params);

    /* Setting the limit and offset for the API call. */
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
      CRM_Utils_System::redirect(
        CRM_Utils_System::url('civicrm/list-org', NULL, FALSE, NULL, FALSE, TRUE)
      );
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

    /* Parameters for the pager. */
    $params = [];
    $params['status'] = ts('Group') . ' %%StatusMessage%%';
    $params['csvString'] = NULL;
    $params['buttonTop'] = 'PagerTopButton';
    $params['buttonBottom'] = 'PagerBottomButton';
    $params['rowCount'] = $this->get(CRM_Utils_Pager::PAGE_ROWCOUNT);

    if (!$params['rowCount']) {
      $params['rowCount'] = Civi::settings()->get('default_pager_size');
    }

    /* Get the total count of organizations based on search parameters. */
    $query = "SELECT count( cc.id ) $fromClause $whereClause";
    $params['total'] = CRM_Core_DAO::singleValueQuery($query, $whereParams);

    /* Create a pager object and assigning it to the template variable 'pager'. */
    $this->_pager = new CRM_Utils_Pager($params);
    $this->assign_by_ref('pager', $this->_pager);
  }

}
