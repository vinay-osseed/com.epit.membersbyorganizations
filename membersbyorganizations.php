<?php

require_once 'membersbyorganizations.civix.php';
// phpcs:disable
use CRM_Membersbyorganizations_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function membersbyorganizations_civicrm_config(&$config) {
  _membersbyorganizations_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function membersbyorganizations_civicrm_install() {
  _membersbyorganizations_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function membersbyorganizations_civicrm_postInstall() {
  /* Creating a message template. */
  try {
    $msg_template = civicrm_api3('MessageTemplate', 'getsingle', [
      'return' => ["id"],
      'msg_title' => "Employee List of Orgnization",
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    $params = [
      'msg_title' => 'Employee List of Orgnization',
      'msg_subject' => 'Employee List of Orgnization',
      'workflow_id' => 1,
      'msg_html' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title></title>
      </head>
      <body>
      <div style="page-break-before: always">
      <h1 align="center">{ts 1=$org_name} %1 {/ts}</h1>
      <h3 align="center">Current Employees</h3>
      <table border="1" style="width: 100%;border-collapse: collapse;">
        <thead>
          <tr>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Membership No</th>
          </tr>
        </thead>
        <tbody>
        {foreach from=$members item=member}
          <tr scope="row">
            <td align="center">{$member.first_name}</td>
            <td align="center">{$member.last_name}</td>
            <td align="center">{$member.membership_id}</td>
          </tr>
        {/foreach}
        </tbody>
      </table>
      </div>
      </body>
      </html>',
      'is_active' => 1
    ];
    $result = civicrm_api3('MessageTemplate', 'create', $params);
    CRM_Core_Error::debug_log_message("Post Install Exception :- " . $e->getMessage(), TRUE);
  }
  _membersbyorganizations_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function membersbyorganizations_civicrm_uninstall() {
/* Deleting the message template created in the post install hook. */
  try {
    $msg_template = civicrm_api3('MessageTemplate', 'getsingle', [
      'return' => ["id"],
      'msg_title' => "Employee List of Orgnization",
    ]);
    $result = civicrm_api3('MessageTemplate', 'delete', [
      'id' => $msg_template['id'],
    ]);
  } catch (CiviCRM_API3_Exception $e) {
    CRM_Core_Error::debug_log_message("Uninstall Exception :- " . $e->getMessage(), TRUE);
  }
  _membersbyorganizations_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function membersbyorganizations_civicrm_enable() {
  _membersbyorganizations_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function membersbyorganizations_civicrm_disable() {
  _membersbyorganizations_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function membersbyorganizations_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membersbyorganizations_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function membersbyorganizations_civicrm_entityTypes(&$entityTypes) {
  _membersbyorganizations_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function membersbyorganizations_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function membersbyorganizations_civicrm_navigationMenu(&$menu) {
  /* Adding a menu item to the navigation menu. */
  _membersbyorganizations_civix_insert_navigation_menu($menu, 'Contacts', [
    'label' => E::ts('List Organizations'),
    'name' => 'list_oganizations',
    'url' => 'civicrm/list-org',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _membersbyorganizations_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_pre().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre
 */
function membersbyorganizations_civicrm_pre($op, $objectName, $id, &$params){
  if ($objectName != "Contribution" || $op != "create") {
    return;
  }

  $org_id = $params['contact_id'];
  try {
    /* Get the organization contact and the message template. */
    $org_contact = civicrm_api3('Contact', 'getsingle', [
      'id' => $org_id,
      'contact_type' => "Organization",
    ]);
    $org_name = $org_contact['display_name'];

    $msg_tpl = civicrm_api3('MessageTemplate', 'getsingle', [
      'msg_title' => "Employee List of Orgnization",
    ]);

    $pdf_name = 'Employees.pdf';
    $tpl_params = [
      'org_name' => $org_name,
    ];
    $members = [];

    /* Get the membership id of the employee. */
    $member_sql = "SELECT
    contact_a.display_name AS `display_name`,
    civicrm_membership.id AS `membership_id`,
    civicrm_membership.owner_membership_id AS `owner_membership_id`
    FROM civicrm_contact contact_a
    LEFT JOIN civicrm_membership ON civicrm_membership.contact_id = contact_a.id
    LEFT JOIN civicrm_contribution_recur ccr ON (civicrm_membership.contribution_recur_id = ccr.id)
    INNER JOIN civicrm_membership_status ON civicrm_membership.status_id = civicrm_membership_status.id
    INNER JOIN civicrm_membership_type ON civicrm_membership.membership_type_id = civicrm_membership_type.id
    WHERE (contact_a.display_name LIKE '%{$org_name}%%'
    AND contact_a.contact_type IN ('Organization')
    AND civicrm_membership.status_id IN ('2', '5') -- Current or Pending
    AND civicrm_membership.is_test = 0)
    AND(1) AND (contact_a.is_deleted = 0)
    GROUP BY civicrm_membership.id;";
    $dao = CRM_Core_DAO::executeQuery($member_sql, CRM_Core_DAO::$_nullArray);

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
        /* If the first and last name is empty, then the display name is assigned as first name. */
        if (empty($member['first_name']) && empty($member['last_name'])) {
          $member['first_name'] = $member['display_name'];
        }
        $members[$member['sort_name']] = [
          'first_name' => $member['first_name'],
          'last_name' => $member['last_name'],
          'membership_id' => $id
        ];
      }
    }

    $rel_contact = civicrm_api3('Relationship', 'get', [
      'sequential' => 1,
      'return' => [
        "id",
        "contact_id_a.display_name",
        "contact_id_a.sort_name",
        "contact_id_a.first_name",
        "contact_id_a.last_name"
      ],
      'contact_id_b' => $org_id,
      'options' => ['limit' => ""]
    ]);

    if ($rel_contact['count']) {
      foreach ($rel_contact['values'] as $con) {
        /* If the first and last name is empty, then the display name is assigned as first name. */
        if (empty($con['contact_id_a.first_name']) && empty($con['contact_id_a.last_name'])) {
          $con['contact_id_a.first_name'] = $con['contact_id_a.display_name'];
        }
        $old_id = $members[$con['contact_id_a.sort_name']]['membership_id'];
        $members[$con['contact_id_a.sort_name']] = [
          'first_name' => $con['contact_id_a.first_name'],
          'last_name' => $con['contact_id_a.last_name'],
          'membership_id' => isset($old_id) ? $old_id : $con['id'],
        ];
      }
    }

    /* Sorting the array by key `sort_name`. */
    ksort($members,SORT_REGULAR);
    $tpl_params['members'] = $members;

  /* Send the message template parameters. */
  $send_tpl_params = [
      'messageTemplateID' =>(int) $msg_tpl['id'],
      'tplParams' => $tpl_params,
      'tokenContext' => ['contactId' => $org_id, 'smarty' => TRUE],
      'PDFFilename' => $pdf_name,
  ];

  /* This is a temporary solution to get the html content of the message template. */
  $session = CRM_Core_Session::singleton();

  /* If there are no members of the organization, then the message template is not sent. */
  if (empty($tpl_params['members'])) {
    $session->reset(['tpl_html']);
    return;
  }

  list($sent, $subject, $message, $html) = CRM_Core_BAO_MessageTemplate::sendTemplate($send_tpl_params);
  $session->set('tpl_html',$html);

  } catch (CiviCRM_API3_Exception $ex) {
    CRM_Core_Error::debug_log_message("Hook `membersbyorganizations_civicrm_pre` Exception :- " . $ex->getMessage(), TRUE);
  }
}

/**
 * Implement hook_civicrm_alterMailContent
 *
 * Replace invoice template with custom content from file
 */
function membersbyorganizations_civicrm_alterMailContent(&$content) {
  if ($content['workflow_name'] != 'contribution_invoice_receipt') {
    return;
  }

  /* This is a temporary solution to get the html content of the message template. */
  $session = CRM_Core_Session::singleton();

  if ($session->isEmpty()) {
    return;
  }

  $tpl_html = $session->get('tpl_html');
  $content['html'] .= $tpl_html;
}