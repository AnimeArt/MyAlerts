<?php
/**
 * Force enable alerts page.
 *
 * Allows the administrator to force enabled alerts to be turned on for all current users.
 *
 * @package MyAlerts
 * @author  Euan Torano <euan@euantor.com>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.1.0
 */

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if (!isset($lang->myalerts)) {
    $lang->load('myalerts');
}

if (!function_exists('myalerts_is_installed') || myalerts_is_installed() != true) {
	flash_message('MyAlerts is not installed!', 'error');
	admin_redirect('index.php?module=myalerts');
}

// start over
$db->delete_query('alert_setting_values');

// get all alert types
$alert_type_query = $db->simple_select('alert_settings');
if ($db->num_rows($alert_type_query) == 0) {
	flash_message('No Alert types detected!', 'error');
	admin_redirect('index.php?module=myalerts');
}

$alert_type_count = 0;
$alert_type_set = array();
while ($alert_id = (int) $db->fetch_field($alert_type_query, 'id')) {
	$alert_type_set[] = $alert_id;
	++$alert_type_count;
}

// get all users
$query = $db->simple_select('users', 'uid');
if ($db->num_rows($query) == 0) {
	flash_message('No users found to enable alerts for!', 'error');
	admin_redirect('index.php?module=myalerts');
}

$user_count = 0;
$settings = array();
while ($uid = (int) $db->fetch_field($query, 'uid')) {
	++$user_count;
	$alert_type_user_set = array();

	foreach ($alert_type_set as $id) {
		$settings[] = array(
			"user_id" => $uid,
			"setting_id" => $id,
			"value" => 1
		);
	}
}
$db->insert_query_multiple('alert_setting_values', $settings);

flash_message("All alert types enabled for every user.<br /><br />Alert Types affected: {$alert_type_count}<br />Total Users affected: {$user_count}", 'success');
admin_redirect('index.php?module=myalerts');