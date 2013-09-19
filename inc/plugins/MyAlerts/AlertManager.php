<?php
/**
 * AlertManager allows the easy adding, removing and editing of alert types for MyAlerts.
 *
 * @package MyAlerts
 * @author  Euan Torano <euan@euantor.com>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.1.0
 */

class AlertManager
{
    /**
     * @var DB_MySQL|DB_MySQLi
     */
    private $db;

    /**
     * @var datacache
     */
    private $cache;

    /**
     * Initialise a new AlertManager instance.
     *
     * @param DB_MySQL|DB_MySQLi $db    A database instance.
     * @param datacache          $cache A datacache instance to store alert types in.
     *
     * @throws InvalidArgumentException
     */
    public function __construct($db, datacache $cache)
    {
        if ($db instanceof DB_MySQL OR $db instanceof DB_MySQLi) {
            $this->db = $db;
        } else {
            throw new InvalidArgumentException();
        }

        $this->cache = $cache;
    }

    /**
     * Add an alert type to MyAlerts.
     *
     * @param array $alertDetails Details about the alert type.
     *
     * @return int The insert ID if available.
     * @throws Exception Thrown if an alert type with the given code already exists in the database.
     */
    public function addAlertType(array $alertDetails)
    {
        $insertArray = array(
            'code'    => $this->db->escape_string($alertDetails['code']),
            'enabled' => (int) $alertDetails['enabled'],
        );

        // Check we don't have an alert type with this code already in the database
        $query = $this->db->simple_select('alert_settings', 'id', "code = '{$insertArray['code']}'");

        if ($this->db->num_rows($query) > 0) {
            throw new Exception('Alert type already exists');
        }

        $insertId = (int) $this->db->insert_query('alert_settings', $insertArray);

        $this->updateAlertTypeCache();

        return $insertId;
    }

    /**
     * Remove an alert type from the database.
     *
     * @param string $code The code of the alert type to remove.
     *
     * @return bool Whather the alert was deleted successfully.
     * @throws Exception Thrown if an alert type with the given code doesn't exist in the database.
     */
    public function deleteAlertType($code = '')
    {
        $code  = $this->db->escape_string($code);
        $query = $this->db->simple_select('alert_settings', 'id', "code = '{$code}'", array('limit' => 1));

        if ($this->db->num_rows($query) != 1) {
            throw new Exception('Alert type does not exist in the database');
        }

        $success = (bool) $this->db->delete_query('alert_settings', "code = '{$code}'", 1);

        $this->updateAlertTypeCache();

        return $success;
    }

    /**
     * Edit an alert type.
     * 
     * @param  array  $alertDetails The new details for the alert type.
     * 
     * @return bool                 Whether the update was successful.
     */
    public function editAlertType(array $alertDetails = array())
    {
        $code = $this->db->escape_string($alertDetails['code']);
        $updateArray = array(
            'enabled' => (int) $alertDetails['enabled'],
        );

        $success = (bool) $this->db->update_query('alert_settings', $updateArray, "code = '{$code}'", 1);

        $this->updateAlertTypeCache();

        return $success;
    }

    /**
     * Enable an alert type.
     * @param  string $code The code of the alert to enable.
     * @return bool         Whether the method was successful.
     */
    public function enableAlertType($code = '')
    {
        return $this->editAlertType(array(
                'code' => $code,
                'enabled' => 1,
            )
        );
    }

    /**
     * Disable an alert type.
     * @param  string $code The code of the alert to disable.
     * @return bool         Whether the method was successful.
     */
    public function disableAlertType($code = '')
    {
        return $this->editAlertType(array(
                'code' => $code,
                'enabled' => 0,
            )
        );
    }

    /**
     * Determine whether an alert type is enabled or not.
     * 
     * @param  string  $code The alert type to check.
     * 
     * @return boolean       Whether the alert is enabled.
     */
    public function isAlertTypeEnabled($code = '')
    {
        $alertTypes = $this->cache->read('myalerts_alert_types');

        return (bool) $alertTypes[$code]['enabled'];
    }

    /**
     * Update the cache of alert types.
     */
    private function updateAlertTypeCache()
    {
        $query   = $this->db->simple_select('alert_settings', '*');
        $toCache = array();

        if ($this->db->num_rows($query) > 0) {
            while ($type = $this->db->fetch_array($query)) {
                $toCache[$type['code']] = $type;
            }

        }

        $this->cache->update('myalerts_alert_types', $toCache);
    }
}
