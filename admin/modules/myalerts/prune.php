<?php
/**
 * Alert pruning page.
 *
 * Allows the manual running of the prune task rather than having to search for it in the task list.
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
