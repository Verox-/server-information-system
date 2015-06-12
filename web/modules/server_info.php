<?php

if (!defined("IN_SIM")) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

require_once 'settings.php';
require_once SIM_ROOT_DIR . 'modules/db.php';

class ServerBasicInfo
{
    var $server_info;
    var $db;

    function __construct()
    {
        // Get the database driver.
        $this->db = DbDriver::getDriver();

        var_dump($this->db->query("SELECT * FROM `sim_server_session` WHERE srv_id='SRV1'")); //" . SIMRegistry::$settings['query'][0]['id'] . "
        // Get all the information we have from the database.
        if (!true) {
            $server_info['error'] = true;
            echo "ERR: Db error." . $this->db->getError();
            return;
        }

        // Check we actually got a result.
        if ($this->db->num_rows() < 1) {
            $server_info['error'] = true;
            echo "ERR: SRVID_NONEXIST.";
            return;
        }

        // Turn it into something useful.
        $this->server_info = $this->db->fetch();

        // Define the extra mission information.
        $mission_info = array(
                                'mission_description' => "<span style='font-style: italic;'>No description found.</span>",
                                'mission_author' => "<span style='font-style: italic;'>No author found.</span>",
                                'mission_reported' => false,
                                'mission_min_players' => 0,
                                'mission_mode' => "<span style='font-style: italic;'>Unknown.</span>",
                                'match' => false
                                );

        // Merge the databse fields to the existing information.
        $this->server_info = array_merge($this->server_info, $mission_info);

        // If there's nobody on the server then there is no mission running. We can just leave here.
        if ($this->server_info['players_current'] <= 0) {
            return;
        }

        // Check if we have a mission currently selected. Prefer filename.
        if (!empty($res['mission_file']))
        {
            if ($retrieved_info = $this->GetCurrentMissionInfo("file", $res['mission_file']))
            {
                $this->server_info['mission_description'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_description']];
                $this->server_info['mission_author'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_author']];
                $this->server_info['mission_reported'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_broken']];
                $this->server_info['mission_min_players'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_min_slots']];
                $this->server_info['mission_mode'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_type']];

                $this->server_info['file_match'] = false;
                $this->server_info['match'] = true;
            }
        }
        else if (!empty($res['mission_name']))
        {
            if ($retrieved_info = $this->GetCurrentMissionInfo("name", $res['mission_name']))
            {
                $this->server_info['mission_description'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_description']];
                $this->server_info['mission_author'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_author']];
                $this->server_info['mission_reported'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_broken']];
                $this->server_info['mission_min_players'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_min_slots']];
                $this->server_info['mission_mode'] = $retrieved_info[SIMRegistry::$settings['ips']['ccs']['mission_type']];

                $this->server_info['match'] = true;
            }
        }
        else
        {
            // We use the default mission_info.
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

        return ($this->db->queryIPB($query)) ? $this->db->fetch() : false ;
    }

    public function GetServerInfo()
    {
        return $this->server_info;
    }
}



?>
