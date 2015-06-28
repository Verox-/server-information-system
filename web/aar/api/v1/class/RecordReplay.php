<?php

// Load the database driver.
require_once SIM_ROOT_DIR . 'modules/db.php';

if (!defined("IN_SIM")) {
	die("SIM did not fully initialise. Did you directly access this file?");
}

class RecordReplay
{
    const REPLAY_DIR = "../../replays/";
    const REPLAYS_TABLE = "aar_replays";

    /**
     * Records the json to file.
     * @param json $json JSON parsed to associative array.
     */
    function Record(&$json)
    {
        // Construct the temprary filename.
        $temp_replay = SIMRegistry::$settings['replay']['dir'] . $json['hash'];

        // Append the file
        file_put_contents($temp_replay , json_encode($json['data']) . "\n", FILE_APPEND);

        // Check if we need to perform specific functions.
        switch ($json['event']) {
            case 'start_mission':
                // Split the mission filename and the island.
                $mission_info = explode(".",$json['data']['mission']);

                // Create a new instance of the DB driver.
                $db = new DbDriver();

                // Insert new record in db.
                $db->insert(self::REPLAYS_TABLE, array(
                    'server' => $json['server_id'],
                    'hash' => $json['hash'],
                    'filename' => $mission_info[0],
                    'island' => $mission_info[count($mission_info) - 1], // Temp hack to work around more than 1 dot.
                    'start' => gmdate("Y-m-d H:i:s"),
                ));

                break;

            case 'end_mission':
                // Create a new instance of the DB driver.
                $db = new DbDriver();

                // Update record with end time
                $db->update(self::REPLAYS_TABLE, array(
                    'end' => gmdate("Y-m-d H:i:s"),
                ), "`hash`='{$json['hash']}'");

                // Compress the replay file.
                $this->gzCompressFile($temp_replay);

                // Delete the temporary file.
                unlink($temp_replay);

                break;
        }
    }

    /**
     * GZIPs a file on disk (appending .replay to the name)
     *
     * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @return string New filename (with .replay appended) if success, or false if operation fails
     */
    function gzCompressFile($source, $level = 9){
        $dest = $source . SIMRegistry::$settings['replay']['extension'];
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($dest, $mode)) {
            if ($fp_in = fopen($source,'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error)
            return false;
        else
            return $dest;
    }
}
