<?php
/**
 * Alert Settings management module for MyAlerts.
 *
 * Allows the enabling/disabling of certain alert types for all users selection.
 *
 * @package MyAlerts
 * @author  Euan Torano <euan@euantor.com>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.1.0
 */

// Disallow direct access to this file for security reasons
if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!isset($lang->myalerts)) {
    $lang->load('myalerts');
}

$page->add_breadcrumb_item($lang->myalerts, 'index.php?module=myalerts');
$page->add_breadcrumb_item($lang->myalerts_nav_settings, 'index.php?module=myalerts-alert_types');

switch ($mybb->input['action']) {
    case 'edit':

        if (strtolower($mybb->request_method) == 'post') {
            if (!verify_post_check($mybb->input['my_post_key'])) {
                flash_message($lang->invalid_post_verify_key, 'error');
                admin_redirect("index.php?module=myalerts-alert_types");
            }

            $id = (int)$mybb->input['id'];

            if (!$id) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-alert_types');
            }

            if (!$id) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-alert_types');
            }

            $query = $db->simple_select('alert_settings', '*', "id='{$id}'", array('limit' => '1'));

            if ($db->num_rows($query) != 1) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-alert_types');
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
                admin_redirect('index.php?module=myalerts-alert_types');
            } else {
                flash_message('Error updating setting', 'error');
                admin_redirect('index.php?module=myalerts-alert_types');
            }
        } else {
            $id = (int)$mybb->input['id'];
            $page->add_breadcrumb_item(
                $lang->edit,
                'index.php?module=myalerts-alert_types&amp;action=edit&amp;id=' . $id
            );
            $page->output_header($lang->edit . ' - ' . $lang->myalerts_nav_settings);

            if (!$id) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-alert_types');
            }

            $query = $db->simple_select('alert_settings', '*', "id='{$id}'", array('limit' => '1'));

            if ($db->num_rows($query) != 1) {
                flash_message($lang->myalerts_error_invalid_setting_id, 'error');
                admin_redirect('index.php?module=myalerts-alert_types');
            }

            $alertType = $db->fetch_array($query);
            unset($query);

            $form = new Form('index.php?module=myalerts-alert_types&amp;action=edit', 'post');
            echo $form->generate_hidden_field('id', $id);
            $formContainer = new FormContainer($lang->edit);
            $formContainer->output_row(
                'Code',
                'An internal code used to represent this alert setting. This is only shown as a reference and cannot be edited.',
                $form->generate_text_box('code', $alertType['code'], array('id' => 'code', 'disabled' => 'disabled')),
                'code'
            );
            $formContainer->output_row(
                'Enabled',
                'Is this alert type enabled for user selection?',
                $form->generate_check_box(
                    'enabled',
                    $alertType['enabled'],
                    'Enabled',
                    array('id' => 'enabled', 'checked' => $alertType['enabled'])
                ),
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

        $query = $db->simple_select('alert_settings', '*');

        $table = new Table;
        $table->construct_header('Title');
        $table->construct_header('Enabled?', array('class' => 'align_center', 'width' => 150));
        $table->construct_header($lang->controls, array('class' => 'align_center', 'width' => 150));

        while ($row = $db->fetch_array($query)) {
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
                'index.php?module=myalerts-alert_types&amp;action=edit&amp;id=' . (int)$row['id']
            );

            $table->construct_cell($controls->fetch(), array('class' => 'align_center'));

            $table->construct_row();
        }
        unset($query);

        $table->output($lang->myalerts_nav_settings);

        break;
}

$page->output_footer();
