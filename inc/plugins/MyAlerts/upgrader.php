<?php

function myalerts_upgrader_run($currentVersion = '1.1.0', $oldVersion = '1.00')
{

    $currentVersion = (string)$currentVersion;
    $oldVersion     = (string)$oldVersion;

    if (($currentVersion == '1.04' AND (double)$oldVersion < 1.04) OR ($currentVersion == '1.1.0' AND (double)$oldVersion < 1.04)) {
        global $db;

        if (!$db->table_exists('alert_settings')) {
            $collation = $db->build_create_table_collation();
            $db->write_query(
                "CREATE TABLE " . TABLE_PREFIX . "alert_settings(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				code VARCHAR(75) NOT NULL
				) ENGINE=MyISAM{$collation};"
            );
        }

        if (!$db->table_exists('alert_setting_values')) {
            $collation = $db->build_create_table_collation();
            $db->write_query(
                "CREATE TABLE " . TABLE_PREFIX . "alert_setting_values(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				user_id INT(10) NOT NULL,
				setting_id INT(10) NOT NULL,
				value INT(1) NOT NULL DEFAULT '1'
				) ENGINE=MyISAM{$collation};"
            );
        }

        if ($db->field_exists('myalerts_settings', 'users')) {
            $db->drop_column('users', 'myalerts_settings');
        }

        if (!$db->field_exists('forced', 'alerts')) {
            $db->add_column('alerts', 'forced', "INT(1) NOT NULL DEFAULT '0'");
        }

        // Settings
        $insertArray = array(
            0 => array(
                'code' => 'rep',
            ),
            1 => array(
                'code' => 'pm',
            ),
            2 => array(
                'code' => 'buddylist',
            ),
            3 => array(
                'code' => 'quoted',
            ),
            4 => array(
                'code' => 'post_threadauthor',
            ),
        );

        $db->insert_query_multiple('alert_settings', $insertArray);
    }

    if ($currentVersion == '1.1.0' AND $oldVersion == '1.04') {
        global $db;

        if (!$db->field_exists('alert_settings', 'enabled')) {
            $db->add_column('alert_settings', 'enabled', "BIT(1) NOT NULL DEFAULT '1'");
        }
    }
}
