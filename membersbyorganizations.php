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
    $msg_tpl = civicrm_api3('MessageTemplate', 'getsingle', [
      'msg_title' => "Employee List of Orgnization",
    ]);

    $pdf_name = 'Employees.pdf';
    $tpl_params = [
      'org_name' => $org_contact['display_name'],
    ];

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
      return;
    }

    /* Get the list of all the employees of the organization. */
    $contacts = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
      'return' => ["first_name", "last_name"],
      'contact_type' => "Individual",
      'api.Membership.get' => [
          'status_id' => ['BETWEEN' => [
              2, // Current
              5, // Pending
          ]],
          'membership_type_id' => $membership_type['id'],
          'relationship_name' => "Employee of",
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
                  'first_name' => $contact['first_name'],
                  'last_name' => $contact['last_name'],
                  'membership_id' => $contact['api.Membership.get']['id'],
                ];
              }
            }
          }
        }
      $tpl_params['members'] = $members;
    }

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