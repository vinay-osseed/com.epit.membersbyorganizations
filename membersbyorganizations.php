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
      'msg_html' => '<p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <hr />
      <p>&nbsp;</p>
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
      </table>',
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

      /* Get the list of all the employees of the organization. */
      $contacts = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'return' => ["first_name", "last_name"],
        'contact_type' => "Individual",
        'api.Membership.get' => [
            'status_id' => ['BETWEEN' => [
                "2", // current
                "5", // pending
            ]],
            'relationship_name' => "Employee of",
        ],
        'options' => ['sort' => "last_name"],
      ]);

      $members = [];
      if ($contacts['count']) {
        foreach ($contacts['values'] as $contact) {
            if ($contact['api.Membership.get']['count']) {
                $members[] = [
                    'first_name' => $contact['first_name'],
                    'last_name' => $contact['last_name'],
                    'membership_id' => $contact['api.Membership.get']['id'],
                ];
            }
        }
        $tpl_params['members'] = $members;
    }

    $send_tpl_params = [
        'messageTemplateID' =>(int) $msg_tpl['id'],
        'tplParams' => $tpl_params,
        'PDFFilename' => $pdf_name,
    ];

    list($sent, $subject, $message, $html) = CRM_Core_BAO_MessageTemplate::sendTemplate($send_tpl_params);

    /* This is a temporary solution to get the html content of the message template. */
    $session = CRM_Core_Session::singleton();
    $session->set('tpl_html',$html);

    // $filename = CRM_Utils_Mail::appendPDF($pdf_name, $html, null)['fullPath'] ?? '';
    // $params['attachFile_2'] = [
    //     'uri' => $filename,
    //     'type' => 'application/pdf',
    //     'location' => $filename,
    //     'upload_date' => date('YmdHis'),
    // ];

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
  if ($content['workflow_name'] === 'contribution_invoice_receipt') {
    /* This is a temporary solution to get the html content of the message template. */
    $session = CRM_Core_Session::singleton();
    $tpl_html = $session->get('tpl_html');
    $content['html'] .= $tpl_html;
  }
}