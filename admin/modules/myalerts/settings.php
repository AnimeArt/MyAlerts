<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if (!isset($lang->myalerts)) {
    $lang->load('myalerts');
}

$page->add_breadcrumb_item($lang->myalerts, 'index.php?module=myalerts');
$page->add_breadcrumb_item($lang->myalerts_nav_settings, 'index.php?module=myalerts-settings');

switch ($mybb->input['action']) {
    case 'edit':

        if (strtolower($mybb->request_method) == 'post') {
            $id = (int)$mybb->input['id'];

            if (!$id) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-settings');
            }

            if (!$id) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-settings');
            }

            $query = $db->simple_select('alert_settings', '*', "id='{$id}'", array('limit' => '1'));

            if ($db->num_rows($query) != 1) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-settings');
            }

            unset($query);

            $enabled = 0;
            if (isset($mybb->input['enabled'])) {
                $enabled = 1;
            }

            if ($db->update_query(
                'alert_settings',
                array(
                     'enabled' => $enabled,
                ),
                "id='{$id}'",
                1
            )
            ) {
                flash_message('Updated', 'success');
                admin_redirect('index.php?module=myalerts-settings');
            } else {
                flash_message('Error updating setting', 'error');
                admin_redirect('index.php?module=myalerts-settings');
            }
        } else {
            $id = (int)$mybb->input['id'];
            $page->add_breadcrumb_item($lang->edit, 'index.php?module=myalerts-settings&amp;action=edit&amp;id=' . $id);
            $page->output_header($lang->edit . ' - ' . $lang->myalerts_nav_settings);

            if (!$id) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-settings');
            }

            $query = $db->simple_select('alert_settings', '*', "id='{$id}'", array('limit' => '1'));

            if ($db->num_rows($query) != 1) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-settings');
            }

            $setting = $db->fetch_array($query);
            unset($query);

            $form = new Form('index.php?module=myalerts-settings&amp;action=edit', 'post');
            echo $form->generate_hidden_field("id", $id);
            $formContainer = new FormContainer($lang->edit);
            $formContainer->output_row(
                'Code',
                'An internal code used to represent this alert setting. This is only shown as a reference and cannot be edited.',
                $form->generate_text_box('code', $setting['code'], array('id' => 'code', 'disabled')),
                'code'
            );
            $formContainer->output_row(
                'Enabled',
                'Is this alert type enabled for user selection?',
                $form->generate_check_box('enabled', $setting['enabled'], 'Enabled', array('id' => 'enabled')),
                'enabled'
            );
            $formContainer->end();

            $buttons[] = $form->generate_submit_button('Update');
            $form->output_submit_wrapper($buttons);
            $form->end();
        }


        break;

    default:
        $page->output_header($lang->myalerts_nav_settings);

        $settings = array();
        $query    = $db->simple_select('alert_settings', '*');

        $table = new Table;
        $table->construct_header('Title');
        $table->construct_header('Enabled?', array('class' => 'align_center', 'width' => 150));
        $table->construct_header($lang->controls, array('class' => 'align_center', 'width' => 150));

        while ($row = $db->fetch_array($query)) {
            $settings[] = $row;

            $table->construct_cell(htmlspecialchars_uni($row['code']));

            $checked = '';
            if ($row['enabled']) {
                $checked = ' checked';
            }

            $enabled = '<input type="checkbox"' . $checked . ' disabled />';
            $table->construct_cell($enabled, array('class' => 'align_center'));

            $controls = new DefaultPopupMenu('myalerts_setting_' . (int)$row['id'] . '_controls', $lang->controls);

            $controls->add_item(
                $lang->edit,
                'index.php?module=myalerts-settings&amp;action=edit&amp;id=' . (int)$row['id']
            );

            $table->construct_cell($controls->fetch(), array('class' => 'align_center'));

            $table->construct_row();
        }
        unset($query);

        $table->output($lang->myalerts_nav_settings);

        break;
}

$page->output_footer();