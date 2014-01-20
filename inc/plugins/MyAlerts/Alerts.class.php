<?php
/**
 *  Main Alerts Class
 *
 * @package MyAlerts
 * @author  Euan Torano <euan@euantor.com>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.1.0
 */

class Alerts
{
    const VERSION = '1.1.0';
	/**
	 * @var MyBB
	 */
	private $mybb;
	/**
	 * @var DB_MySQLi
	 */
	private $db;

	/**
     *  Constructor
     *
	 * @param                    MyBB MyBB Object
	 * @param DB_MySQL|DB_MySQLi MyBB Database Object
	 *
	 * @throws Exception
	 */
    public function __construct($mybbIn, $dbIn)
    {
		if ($mybbIn instanceof MyBB AND ($dbIn instanceof DB_MySQL OR $dbIn instanceof DB_MySQLi)) {
			$this->mybb = $mybbIn;
            $this->db   = $dbIn;
        } else {
            throw new Exception('You must pass valid $mybb and $db objects as parameters to the Alerts class');
        }
    }

    /**
     *  Get the number of alerts a user has
     *
     * @return int The total number of alerts the user has
     */
    public function getNumAlerts()
    {

        static $numAlerts;

        if (!is_int($numAlerts)) {
            $numAlerts = 0;

            if (is_array($this->mybb->user['myalerts_settings'])) {
                $alertTypes = "'" . implode(
                        "','",
                        array_keys(array_filter((array)$this->mybb->user['myalerts_settings']))
                    ) . "'";

                $num       = $this->db->simple_select(
                    'alerts',
                    'COUNT(id) AS count',
                    '(alert_type IN (' . $alertTypes . ') OR forced = 1) AND uid = ' . (int)$this->mybb->user['uid']
                );
                $numAlerts = (int)$this->db->fetch_field($num, 'count');
            }
        }

        return $numAlerts;
    }

    /**
     *  Get the number of unread alerts a user has
     *
     * @return int The number of unread alerts
     */
    public function getNumUnreadAlerts()
    {
        static $numUnreadAlerts;

        if (!is_int($numUnreadAlerts)) {
            $numUnreadAlerts = 0;

            if (is_array($this->mybb->user['myalerts_settings'])) {
                $alertTypes      = "'" . implode(
                        "','",
                        array_keys(array_filter((array)$this->mybb->user['myalerts_settings']))
                    ) . "'";
                $num             = $this->db->simple_select(
                    'alerts',
                    'COUNT(id) AS count',
                    'uid = ' . (int)$this->mybb->user['uid'] . ' AND unread = 1 AND (alert_type IN (' . $alertTypes . ') OR forced = 1)'
                );
                $numUnreadAlerts = (int)$this->db->fetch_field($num, 'count');
            }
        }

        return $numUnreadAlerts;
    }

    /**
     *  Fetch all alerts for the currently logged in user
     *
	 * @param int $start The start point (used for multipaging alerts)
	 * @param int $limit The number of alerts to retrieve.
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	public function getAlerts($start = 0, $limit = 0)
	{
        if ((int)$this->mybb->user['uid'] > 0) { // check the user is a user and not a guest - no point wasting queries on guests afterall
            if ($limit == 0) {
                $limit = $this->mybb->settings['myalerts_perpage'];
            }

            $alertTypes = "'" . implode(
                    "','",
                    array_keys(array_filter((array)$this->mybb->user['myalerts_settings']))
                ) . "'";

            $alerts = $this->db->write_query(
                "SELECT a.*, u.uid, u.username, u.avatar, u.usergroup, u.displaygroup FROM " . TABLE_PREFIX . "alerts a INNER JOIN " . TABLE_PREFIX . "users u ON (a.from_id = u.uid) WHERE a.uid = " . (int)$this->mybb->user['uid'] . " AND (alert_type IN ({$alertTypes}) OR a.forced = 1) ORDER BY a.id DESC LIMIT " . (int)$start . ", " . (int)$limit . ";"
            );
            if ($this->db->num_rows($alerts) > 0) {
                $return = array();
                while ($alert = $this->db->fetch_array($alerts)) {
                    $alert['content'] = json_decode($alert['content'], true);
                    $return[]         = $alert;
                }

                return $return;
            } else {
                return false;
            }
        } else {
            throw new Exception('Guests have not got access to the Alerts functionality');
        }
    }

    /**
     *  Fetch all unread alerts for the currently logged in user
     *
	 * @return array|boolean When the user has unread alerts
	 * @throws Exception
	 */
    public function getUnreadAlerts()
    {
        if ((int)$this->mybb->user['uid'] > 0) { // check the user is a user and not a guest - no point wasting queries on guests afterall
            $alertTypes = "'" . implode(
                    "','",
                    array_keys(array_filter((array)$this->mybb->user['myalerts_settings']))
                ) . "'";
            $alerts     = $this->db->write_query(
                "SELECT a.*, u.uid, u.username, u.avatar FROM " . TABLE_PREFIX . "alerts a INNER JOIN " . TABLE_PREFIX . "users u ON (a.from_id = u.uid) WHERE a.uid = " . (int)$this->mybb->user['uid'] . " AND unread = '1' AND (a.alert_type IN({$alertTypes}) OR a.forced = 1) ORDER BY a.id DESC;"
            );

            if ($this->db->num_rows($alerts) > 0) {
                $return = array();
                while ($alert = $this->db->fetch_array($alerts)) {
                    $alert['content'] = json_decode($alert['content'], true);
                    $return[]         = $alert;
                }

                return $return;
            } else {
                return false;
            }
        } else {
            throw new Exception('Guests have not got access to the Alerts functionality');
        }
    }

    /**
     *  Mark alerts as read
     *
	 * @param string|array $alerts Either a string formatted for use in a MySQL IN() clause or an array to be parsed into said form
	 */
    public function markRead($alerts = '')
    {
        $alerts = (array)$alerts;

        if (is_array($alerts)) {
            $alerts = array_map('intval', $alerts);
            $alerts = "'" . implode("','", $alerts) . "'";
        }

        return $this->db->update_query(
            'alerts',
            array(
                 'unread' => '0'
            ),
            'id IN(' . $alerts . ') AND uid = ' . $this->mybb->user['uid']
        );
    }

    /**
     *  Delete alerts
     *
     * @param String/Array Either a string formatted for use in a MySQL IN() clause or an array to be parsed into said form
     */
    public function deleteAlerts($alerts = '')
    {
        if (is_array($alerts) OR is_int($alerts)) {
            $alerts = (array)$alerts;
            $alerts = array_map('intval', $alerts);
            $alerts = "'" . implode("','", $alerts) . "'";

            return $this->db->delete_query(
                'alerts',
                'id IN(' . $alerts . ') AND uid = ' . (int)$this->mybb->user['uid']
            );
        } else {
            if ($alerts == 'allRead') {
                return $this->db->delete_query('alerts', 'unread = 0 AND uid = ' . (int)$this->mybb->user['uid']);
            } elseif ($alerts = 'allAlerts') {
                return $this->db->delete_query('alerts', 'uid = ' . (int)$this->mybb->user['uid']);
            }
        }
    }

    /**
     *  Add an alert
     *
	 * @param int    $uid     UID to add Alert for
	 * @param string $type    The type of alert
	 * @param int    $tid     The TID - default to 0
	 * @param int    $from    The UID sending the alert.
	 * @param Array  $content Alert content
	 * @param int    $forced  Whether to force the alert to display or not.
	 *
     * @return boolean
     */
    public function addAlert($uid, $type = '', $tid = 0, $from = 0, $content = array(), $forced = 0)
    {
        // first of all, start the session if not started yet
        if (!session_id()) {
            session_start();
        }

        $cache = $_SESSION['myalerts'][$uid];

        if (!empty($tid)) {
            // if tid and type coincide with the respective ones in the $_SESSION array, then do nothing and save multiple notifications to the user
            if ($tid == $cache['tid'] AND $type != $cache['type']) {
                return;
            } else { // there's an unrelated alert here, so unset the previous stored tid and type and store them again, then alert the user as usual
                unset($_SESSION['myalerts'][$uid]);
                $_SESSION['myalerts'][$uid] = array(
                    "tid"  => $tid,
                    "type" => $type
                );
            }
        }

        $content     = json_encode($content);
        $insertArray = array(
            'uid'        => (int)$uid,
            'dateline'   => TIME_NOW,
            'alert_type' => $this->db->escape_string($type),
            'tid'        => (int)$tid,
            'from_id'    => (int)$from,
            'forced'     => (int)$forced,
            'content'    => $this->db->escape_string($content)
        );

        $this->db->insert_query('alerts', $insertArray);
    }

    /**
     *  Add an alert for multiple users
     *
	 * @param array  $uids    UIDs to add alert for
	 * @param string $type    The type of alert
	 * @param int    $tid     The TID - default to 0
	 * @param int    $from    The UID sending the alert.
	 * @param Array  $content Alert content
	 * @param int    $forced  Whether to force the alert to display or not.
	 *
     * @return boolean
     */
    public function addMassAlert($uids, $type = '', $tid = 0, $from = 0, $content = array(), $forced = 0)
    {
        // first of all, start the session if not started yet
        if (!session_id()) {
            session_start();
        }

        $content     = json_encode($content);
        $insertArray = array();

        foreach ($uids as $uid) {

            $cache = $_SESSION['myalerts'][$uid];

            if (!empty($tid)) {
                // if tid and type coincide with the respective ones in the $_SESSION array, then do nothing and save multiple notifications to the user
                if ($tid == $cache['tid'] AND $type != $cache['type']) {
                    return;
                } else { // there's an unrelated alert here, so unset the previous stored tid and type and store them again, then alert the user as usual
                    unset($_SESSION['myalerts'][$uid]);
                    $_SESSION['myalerts'][$uid] = array(
                        "tid"  => $tid,
                        "type" => $type
                    );
                }
            }
            $insertArray[] = array(
                'uid'        => (int)$uid,
                'dateline'   => (int)TIME_NOW,
                'alert_type' => $this->db->escape_string($type),
                'tid'        => (int)$tid,
                'from_id'    => (int)$from,
                'content'    => $this->db->escape_string($content),
                'forced'     => (int)$forced
            );
        }

        $this->db->insert_query_multiple('alerts', $insertArray);
    }
}
