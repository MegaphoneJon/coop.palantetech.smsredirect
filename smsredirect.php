<?php

require_once 'smsredirect.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function smsredirect_civicrm_config(&$config) {
  _smsredirect_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function smsredirect_civicrm_xmlMenu(&$files) {
  _smsredirect_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function smsredirect_civicrm_install() {
  _smsredirect_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function smsredirect_civicrm_uninstall() {
  _smsredirect_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function smsredirect_civicrm_enable() {
  _smsredirect_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function smsredirect_civicrm_disable() {
  _smsredirect_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function smsredirect_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _smsredirect_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function smsredirect_civicrm_managed(&$entities) {
  _smsredirect_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function smsredirect_civicrm_caseTypes(&$caseTypes) {
  _smsredirect_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function smsredirect_civicrm_angularModules(&$angularModules) {
_smsredirect_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function smsredirect_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _smsredirect_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function smsredirect_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function smsredirect_civicrm_navigationMenu(&$menu) {
  _smsredirect_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'coop.palantetech.smsredirect')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _smsredirect_civix_navigationMenu($menu);
} // */

function smsredirect_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // "From" and "To" refer to the outgoing SMS.
  // Therefore, an incoming SMS comes from the "From" person.
  // It goes to the case manager, who is the "To" person.
  if ($op == 'create' && $objectName == 'Activity') {
    $inboundId = smsredirect_getActivityTypeId('Inbound SMS');
    if ($objectRef->activity_type_id == $inboundId) {
      $fromInfo = smsredirect_getTargetInfo($objectId);
      $displayName = $fromInfo['contact_id.display_name'];
      $caseManagerInfo = smsredirect_getCaseManagerInfo($fromInfo['contact_id']);
      $body = "From $displayName: $objectRef->details";
      //Make sure we're not going over 460 characters.
      $body = substr($body, 0, 459);
      foreach ($caseManagerInfo as $toId => $phone) {
        smsredirect_send($body, $toId, $phone);
      }
    }
  }
}

/**
 * Given an activity type (e.g. "Meeting"), return its activity type ID.
 */
function smsredirect_getActivityTypeId($activityType) {
  $result = civicrm_api3('Activity', 'getoptions', array(
    'field' => "activity_type_id",
  ));
  $activityTypeIds = array_flip($result['values']);
  return $activityTypeIds[$activityType];
}

/**
 * Given a contact ID, return the case manager's contact ID and phone number.
 */
function smsredirect_getCaseManagerInfo($id) {
  $info = array();
  // Get the case manager(s).
  $result = civicrm_api3('Relationship', 'get', array(
    'sequential' => 1,
    'return' => array("contact_id_b"),
    'contact_id_a' => $id,
    'relationship_type_id' => 9, //Ugh, magic number for Case Coordinator rel'n.  Could do a lookup later.
  ));
  // Get a mobile phone.  I don't care if there's multiple, just get one.
  foreach ($result['values'] as $contact) {
    $result = civicrm_api3('Phone', 'get', array(
      'sequential' => 1,
      'contact_id' => $contact['contact_id_b'],
      'phone_type_id' => "Mobile",
    ));
    // Only get a number that exists.
    if ($result['count']) {
      $info[$contact['contact_id_b']] = $result['values'][0]['phone'];
    }
    // This implicitly removes duplicates.
  }
  return $info;
}
/**
 * Given an activity ID, get the target's display name and contact ID.
 */
function smsredirect_getTargetInfo($id) {
  $result = civicrm_api3('ActivityContact', 'getsingle', array(
    'return' => array(
      "contact_id.display_name",
      "contact_id",
    ),
    'activity_id' => $id,
    'record_type_id' => "Activity Targets",
  ));
  return $result;
}

function smsredirect_send($body, $toId, $phone) {
  $smsParams = array(
    'to' => "${toId}::$phone",
    'sms_provider_id' => 1, // Hardcoding the SMS provider.
    'provider_id' => 1, // Same here.
    'To' => $phone,
  );
  CRM_Activity_BAO_Activity::sendSMSMessage($toId, $body , $smsParams, NULL, $toId);
}
