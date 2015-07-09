<?php

class LiveCache
{
    // Constant key identifiers.
    const REDIS_NAMESPACE = ":namespace";
    const SERVER_FPS = ":fps";
    const SERVER_UNIT_COUNT = ":units";
    const MISSION_HASH = ":mission:hash";
    const MISSION_FILENAME = ":mission:filename";
    const MISSION_ISLAND = ":mission:island";
    const MISSION_NAME = ":mission:name";

    const UNIT_PREFIX = "UNIT:";
    const UNIT_POSITION = "pos";
    const UNIT_DIRECTION = "dir";
    const UNIT_FACTION = "fac";
    const UNIT_UNIQUE_ID = "uid";
    const UNIT_NAME = "name";
    const UNIT_GROUP = "group";

    private $redis;

    /**
     * LiveCache Constructor
     * @param string  $host     URL, IP or UNIX socket to Redis instance.
     * @param integer $port     [Opt] Port redis instance is operating on
     * @param string  $password [Opt] Password to authenticate to redis with.
     */
    function __construct($host = "localhost", $port = 6379, $password = null) {

        $this->redis = new Redis();

        if ($port != 6379)
        {
            $this->redis->connect($host, $port);
        }
        else
        {
            $this->redis->connect($host);
        }

        if ($password != null)
        {
            $this->redis->auth($password);
        }

    }

    function __destruct()
    {
        $this->redis->close();
    }

    /**
     * Updates redis cache based on event from recieved json.
     * @param array $data JSON decoded to an associative array.
     */
    public function Update(&$data)
    {
        switch ($data['event']) {
            case 'update':
                $this->UpdateUnits($data);
                break;
            case 'start_mission':
                $this->StartMission($data);
                break;
            case 'end_mission':
                $this->EndMission($data);
                break;
            default:
                throw new Exception('Unknown event: ' . $event);
                break;
        }

        // Reselect the default database for metrics update.
        $this->redis->select(0);

        // Update metrics.
        $this->redis->set($data['server_id'] . self::SERVER_FPS, $data['fps']);

        if (array_key_exists("units",$data['data'])) {
            $this->redis->set($data['server_id'] . self::SERVER_UNIT_COUNT, count($data['data']['units']));
        }
    }

    /**
     * Routine on start_mission event
     * @param array $json JSON decoded in associative array.
     */
    private function StartMission(&$json)
    {
        // Split the mission filename and the island.
        $mission_info = explode(".",$json['data']['mission']);

        // Set data for the new mission.
        $this->redis->set($json['server_id'] . self::MISSION_HASH, $json['hash']);
        $this->redis->set($json['server_id'] . self::MISSION_FILENAME, $mission_info[0]);
        $this->redis->set($json['server_id'] . self::MISSION_ISLAND, $mission_info[count($mission_info) - 1]);

        // Select the namespace.
        $this->SelectServerNamespace($json['server_id'], true);

        // Nuke the namespace to get rid of any leftover data.
        $this->redis->flushDB();

        // Look up the filename in the mission database.
        // Set <SRVID>:mission:name to the name of the mission.

        // Insert record to replays table ID (autoinc,primary), HASH, ISLAND, MISSION, NAME, FILENAME, START (GMT).
    }

    private function UpdateUnits(&$json)
    {
        // Select the namespace.
        $this->SelectServerNamespace($json['server_id']);

        // Switch to pipeline mode.
        $this->redis->multi();

        // Loop all the units and add hashes of data.
        foreach ($json['data']['units'] as $unit)
        {
            // Create the array of values to fill the hash.
            $members = array(
                self::UNIT_POSITION  => $unit['pos'],
                self::UNIT_DIRECTION => $unit['dir'],
                self::UNIT_FACTION   => $unit['fac'],
                self::UNIT_UNIQUE_ID => $unit['uid'],
                self::UNIT_NAME => $unit['name'],
                self::UNIT_GROUP => $unit['group'],
            );

            // Add the hash.
            $this->redis->hmset(self::UNIT_PREFIX . $unit['nid'], $members);
            $this->redis->setTimeout(self::UNIT_PREFIX . $unit['nid'], 10); // Unit has 10 seconds to send an update before it expires.
        }

        // Execute the transaction & exit pipeline mode.
        $this->redis->exec();
    }

    // Ends the mission and cleans up unit data.
    private function EndMission(&$json)
    {
        // Delete current mission data.
        $this->redis->del($json['server_id'] . self::MISSION_HASH);
        $this->redis->del($json['server_id'] . self::MISSION_FILENAME);
        $this->redis->del($json['server_id'] . self::MISSION_ISLAND);

        // Select the namespace.
        $this->SelectServerNamespace($json['server_id']);

        // Nuke the namespace to get rid of any leftover data.
        $this->redis->flushDB();
    }

    // Gets the namespace this serverid is using. Creates if nonexistant only if param2 set true.
    private function SelectServerNamespace($serverid, $createOnNonExist = false)
    {
        if (!$namespace = $this->redis->get($serverid . self::REDIS_NAMESPACE))
        {
            // echo "ITNS\n";
            if ($createOnNonExist)
            {
                // echo "ITNE\n";
                $keyspace = $this->redis->info("KEYSPACE");

                // var_dump($keyspace);
                // echo "\n";
                // Redis database is literally empty, just use 1.

                foreach ($keyspace as $key => $info)
                {
                    $db_id = filter_var($key, FILTER_SANITIZE_NUMBER_INT); // Strips out any letters in the key.
// echo "ITFE\n";
                    // We don't want namespace 0 to be used.
                    if ($db_id == 0)
                    {
                        continue;
                        echo "ITzC\n";
                    }
                    else
                    {
                        $this->redis->set($serverid . self::REDIS_NAMESPACE, $db_id);
                        $this->redis->select($db_id);
                        echo "FAT";
                        return $db_id;

                    }
                }

                if (empty($keyspace) || (count($keyspace) == 1 && array_key_exists("db0", $keyspace)))
                {
                    // echo "ITEE\n";
                    $this->redis->set($serverid . self::REDIS_NAMESPACE, 1);
                    $this->redis->select(1);
                    // echo "CREATED AND SET NAMESPACE 1";
                    return 1;
                }
            }
            else
            {
                throw new Exception("Could not find valid namespace for " . $serverid);
            }
        }
        else
        {
            $this->redis->select($namespace);
            return $namespace;
            echo "found??";
        }
    }
}
