<?php

require_once 'membersbyorganizations.civix.php';
// phpcs:disable
use CRM_Membersbyorganizations_ExtensionUtil as E;
use Civi\Token\TokenProcessor;
// phpcs:enable

define("TPL_TITLE", "Employee List of Orgnization");
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
      'msg_title' => TPL_TITLE,
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
      <div style="{$style}">
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
        {foreach from=$members key=index item=member name=count}
          <tr scope="row">
            <td align="center">{$member.first_name}</td>
            <td align="center">{$member.last_name}</td>
            <td align="center" id="{$smarty.foreach.count.index}">{$member.membership_id}</td>
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
      'msg_title' => TPL_TITLE,
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
 * User function for get the list of org employees.
 */
function get_list($org_id) {
  /* Get the organization contact and the message template. */
  $org_contact = civicrm_api3('Contact', 'getsingle', [
    'id' => $org_id,
    'contact_type' => "Organization",
  ]);
  $org_name = $org_contact['display_name'];
  $name = CRM_Utils_Type::escape($org_name, 'String');

  $msg_tpl = civicrm_api3('MessageTemplate', 'getsingle', [
    'msg_title' => TPL_TITLE,
  ]);

  $pdf_name = 'Employees.pdf';
  $tpl_params = [
    'org_name' => $org_name,
    'style' => 'page-break-before: always',
  ];
  $members = [];

  $tokenProcessor = new TokenProcessor(\Civi::dispatcher(), [
    /* Give a unique identifier for this instance. */
    'controller' => __CLASS__,
    /* Enable or disable handling of Smarty notation. */
    'smarty' => FALSE,
    /* List any data fields that we plan to provide. */
    'schema' => ['contactId'],
  ]);

  /* Get a list of contacts who are employees of the organization. */
  $rel_contacts = \Civi\Api4\Contact::get()
  ->addSelect('display_name', 'sort_name', 'first_name', 'last_name', 'membership.id', 'entity_tag.tag_id:label')
  ->addJoin('Membership AS membership', 'LEFT', ['membership.contact_id', '=', 'id'])
  ->addJoin('ContributionRecur AS contribution_recur', 'LEFT', ['membership.contribution_recur_id', '=', 'contribution_recur.id'])
  ->addJoin('MembershipStatus AS membership_status', 'LEFT', ['membership.status_id', '=', 'membership_status.id'])
  ->addJoin('Relationship AS relationship', 'LEFT', ['relationship.contact_id_a', '=', 'id'])
  ->addJoin('Contact AS contact', 'LEFT', ['contact.id', '=', 'relationship.contact_id_b'])
  ->addJoin('EntityTag AS entity_tag', 'LEFT', ['entity_tag.entity_id', '=', 'relationship.contact_id_a'], ['entity_tag.entity_table', '=', "'civicrm_contact'"])
  ->addGroupBy('id')
  ->addWhere('contact.sort_name', 'LIKE', "%{$name}%")
  ->addWhere('relationship.is_active', '=', TRUE)
  ->addWhere('contact.is_deleted', '=', FALSE)
  ->addWhere('relationship.relationship_type_id', '=', 5) // Employee
  ->addWhere('membership.status_id', 'IN', [2, 5]) // Current & Pending
  ->addWhere('membership.is_test', '=', FALSE)
  ->addWhere('is_deleted', '=', FALSE)
  ->addOrderBy('sort_name', 'ASC')
  ->execute();

  $session = CRM_Core_Session::singleton();
  if (count($rel_contacts) == 0) {
    $session->reset(1);
    return;
  }

  foreach ($rel_contacts as $contact) {
    /* Adding a row to the token processor. */
    $tokenProcessor->addRow(['contactId' => $contact['id']]);

    /* This is a workaround to exclude the contacts who have the tag "Not for renewal". */
    if ($contact['entity_tag.tag_id:label'] == "Not for renewal") {
    continue;
    }

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
  $tpl_params['members'] = $members;

  /* Get the token from template . */
  $pattern = "/{contact.*}/i";
  preg_match_all($pattern, $msg_tpl['msg_html'],$matches);
  $token = implode("",$matches[0]);

  /* Define the message template. */
  $tokenProcessor->addMessage('token_value','<td align="center">'.$token.'</td>', 'text/html');

  /* Evaluate any tokens which are referenced in the message. */
  $tokenProcessor->evaluate();
  foreach ($tokenProcessor->getRows() as $row) {
    $mem_no[] = $row->render('token_value');
  }

  /* Send the message template parameters. */
  $send_tpl_params = [
    'messageTemplateID' =>(int) $msg_tpl['id'],
    'tplParams' => $tpl_params,
    'tokenContext' => ['contactId' => $org_id, 'smarty' => TRUE],
    'PDFFilename' => $pdf_name,
  ];
  list($sent, $subject, $message, $html) = CRM_Core_BAO_MessageTemplate::sendTemplate($send_tpl_params);

  /* This is a workaround to replace the token with the membership number. */
  foreach ($mem_no as $key => $value) {
    $html = str_replace('<td align="center" id="'.$key.'"></td>', $value, $html);
  }
  $session->set('tpl_html', $html);
}

/**
 * User function to check if contact_id is of `organization` or not.
 */
function is_org_id($id) {
  $org = \Civi\Api4\Contact::get()
  ->addWhere('id', '=', $id)
  ->addWhere('contact_type', '=', 'Organization')
  ->execute();

  if (count($org) == 0) {
    return FALSE;
  }
  return TRUE;
}

/**
 * Implements hook_civicrm_links().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_links
 */
function membersbyorganizations_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  if (empty($values)) {
    return NULL;
  }

  switch ($op) {
    case 'contribution.selector.row':
      $cid = $values['cid'];
      if (is_org_id($cid)) {
        get_list($cid);
      }
      break;

    case 'Payment.edit.action':
      /* Getting the contact_id of the contribution. */
      $org = \Civi\Api4\Contribution::get()
      ->addSelect('contact_id')
      ->addWhere('id', '=', $values['contribution_id'])
      ->addWhere('contact_id.contact_type', '=', 'Organization')
      ->execute();
      if (count($org) != 0) {
        $cid = $org[0]['contact_id'];
      }
      if (is_org_id($cid)) {
        get_list($cid);
      }
      break;

    default:
      $session = CRM_Core_Session::singleton();
      $session->reset(1);
      break;
  }
  return NULL;
}

/**
 * Implements hook_civicrm_pre().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre
 */
function membersbyorganizations_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName != "Contribution" || $op != "create") {
    return;
  }

  if (is_org_id($params['contact_id'])) {
    get_list($params['contact_id']);
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

  $session = CRM_Core_Session::singleton();
  if ($session->isEmpty()) {
    return;
  }

  $tpl_html = $session->get('tpl_html');
  $content['html'] .= $tpl_html;
}