<?php

if (!defined("IN_SIM")) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

require_once 'settings.php';
require_once SIM_ROOT_DIR . 'modules/db.php';

class ServerBasicInfo
{
    var $server_info;
    var $db = DbDriver::getDriver;

    function __construct()
    {
        // Get all the information we have from the database.
        if (!$db->query("SELECT * FROM `sim_server_session` WHERE srv_id='" . SIMRegistry::$settings['query'][0]['id'] . "'")) {
            $server_info['error'] = true;
        }

        // Check we actually got a result.
        if ($db->num_rows < 1) {
            $server_info['error'] = true;
        }

        // Turn it into something useful.
        $res = $db->fetch_assoc();

        // Check if we have a mission currently selected. Prefer filename.
        if (!empty($res['mission_file']))
        {

        }
        else if (!empty($res['mission_name']))
        {

        }
    }

    private function GetCurrentMissionInfo($type, $mission)
    {
        if ($type == "file")
        {
            $query = "  SELECT *
                        FROM `".SIMRegistry::$settings['ips']['ccs']['db']."`
                        WHERE ".SIMRegistry::$settings['ips']['ccs']['mission_file']."='{$mission}'
                            AND category_id=".SIMRegistry::$settings['ips']['ccs']['category']."
                    ";
        }
        else
        {
            $query = "  SELECT *
                        FROM `".SIMRegistry::$settings['ips']['ccs']['db']."`
                        WHERE ".SIMRegistry::$settings['ips']['ccs']['mission_name']."='{$mission}'
                            AND category_id=".SIMRegistry::$settings['ips']['ccs']['category']."
                    ";
        }

        return ($db->queryIPB($query)) ? $db->fetch_assoc() : false ;
    }

    public function GetServerInfo()
    {
        return $server_info;
    }
}



?>
