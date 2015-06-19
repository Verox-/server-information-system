<?php
// Prepend a base path if Predis is not available in your "include_path".
//require 'Predis/Autoloader.php';

// Predis\Autoloader::register();
//
// $client = new Predis\Client();
// $client->set('foo', 'bar');
// $value = $client->get('foo');

// ------- On start_mission --------
// Set <SRVID>:mission:hash to the current mission hash.
// Set <SRVID>:mission:filename to the current mission filename.
// Set <SRVID>:mission:island to the current mission island.

// Get the namespace this server is operating in in the root redis namespace, if it doesn't exist, create it.
// Switch to that namespace.
// Nuke it. (clear all existing keys if they exist.)

// Look up the filename in the mission database.
// Set <SRVID>:mission:name to the name of the mission.

// Insert record to replays table ID (autoinc,primary), HASH, ISLAND, MISSION, NAME, FILENAME, START (GMT).



// ------- On update --------
// Get the namespace we're operating in from the root redis namespace.
// Switch to that namespace.
// Switch into pipeline (transactional) mode.
// Start loop of all units.
//// Get the network ID of the thing, it's going to be the key.
//// Put the data we're storing into array. key=>value. Location, direction, faction, uid. https://github.com/phpredis/phpredis/blob/develop/README.markdown#hmset
//// HMSET key hash
// End loop of all units.
// Execute pipeline and exit transactional mode.
// Append unit data to the replay file.

$json = file_get_contents("php://input"); // What the fuck, php...

// Decode the json.
$decoded_json = json_decode($json, true);

// Init the live cache handler and perform the update.
$test = new LiveCache();
$test->Update($decoded_json);

class LiveCache
{
    // Constant key identifiers.
    const REDIS_NAMESPACE = ":namespace";
    const MISSION_HASH = ":mission:hash";
    const MISSION_FILENAME = ":mission:filename";
    const MISSION_ISLAND = ":mission:island";
    const MISSION_NAME = ":mission:name";

    const UNIT_POSITION = "pos";
    const UNIT_DIRECTION = "dir";
    const UNIT_FACTION = "fac";
    const UNIT_UNIQUE_ID = "uid";

    private $redis;

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
    }

    private function StartMission(&$json)
    {
        // Split the mission filename and the island.
        $mission_info = explode(".",$json['data']['mission']);

        // Set data for the new mission.
        $this->redis->set($json['server_id'] . self::MISSION_HASH, $json['hash']);
        $this->redis->set($json['server_id'] . self::MISSION_FILENAME, $mission_info[0]);
        $this->redis->set($json['server_id'] . self::MISSION_ISLAND, $mission_info[1]);

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
            );

            // Add the hash.
            $this->redis->hmset($unit['nid'], $members);
            $this->redis->setTimeout($unit['nid'], 10); // Unit has 10 seconds to send an update before it expires.
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

//debug.
file_put_contents("test.txt", $json, FILE_APPEND);

 ?>
