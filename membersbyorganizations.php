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
  _membersbyorganizations_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function membersbyorganizations_civicrm_uninstall() {
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
