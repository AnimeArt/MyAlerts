<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function myalerts_meta()
{
    global $page, $lang, $plugins;

    if (!isset($lang->myalerts)) {
        $lang->load('myalerts');
    }

    $sub_menu       = array();
    $sub_menu['10'] = array(
        "id"    => "settings",
        "title" => $lang->myalerts_nav_settings,
        "link"  => "index.php?module=myalerts-settings"
    );
    $sub_menu['20'] = array(
        "id"    => "force_alerts",
        "title" => $lang->myalerts_nav_force_alerts,
        "link"  => "index.php?module=myalerts-force_alerts"
    );
    $sub_menu['30'] = array(
        "id"    => "prune",
        "title" => $lang->myalerts_nav_prune,
        "link"  => "index.php?module=myalerts-prune"
    );

    $sub_menu = $plugins->run_hooks("myalerts_admin_menu", $sub_menu);

    $page->add_menu_item($lang->myalerts, "myalerts", "index.php?module=myalerts", 50, $sub_menu);

    return true;
}

function myalerts_action_handler($action)
{
    global $page, $plugins;

    $page->active_module = "myalerts";

    $actions = array(
        'settings'     => array('active' => 'settings', 'file' => 'settings.php'),
        'force_alerts' => array('active' => 'force-alerts', 'file' => 'force_alerts.php'),
        'prune'        => array('active' => 'prune', 'file' => 'prune.php'),
    );

    $actions = $plugins->run_hooks("maylerts_admin_action_handler", $actions);

    if (isset($actions[$action])) {
        $page->active_action = $actions[$action]['active'];

        return $actions[$action]['file'];
    } else {
        $page->active_action = "settings";

        return "settings.php";
    }
}

function myalerts_admin_permissions()
{
    global $lang, $plugins;

    if (!isset($lang->myalerts)) {
        $lang->load('myalerts');
    }

    $admin_permissions = array(
        "settings"     => $lang->myalerts_admin_perm_can_settings,
        "force_alerts" => $lang->myalerts_admin_perm_can_force_alerts,
        "prune"        => $lang->myalerts_admin_perm_can_prune,
    );

    $admin_permissions = $plugins->run_hooks("myalerts_admin_permissions", $admin_permissions);

    return array("name" => $lang->myalerts, "permissions" => $admin_permissions, "disporder" => 50);
}
