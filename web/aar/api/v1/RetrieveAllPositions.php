<?php
//$srvid = $_GET['server'];
$srvid = "SRV1";

// Set the headers.
header('Content-Type: text/event-stream');

// Connect to redis.
$redis = new Redis();
$redis->connect('localhost');

// Select the right database.
$s_namespace = $redis->get($srvid . ":namespace");
$redis->select($s_namespace);

// Set the SCAN options.
$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY); /* retry when we get no keys back */

// Start the work loop.
while (true) {
    // Wipe the array, just to be sure.
    $unitsArray = array();
    $it = NULL; /* Initialize our iterator to NULL */
    while($arr_keys = $redis->scan($it, "UNIT:*")) {
        foreach($arr_keys as $str_key) {
            //echo "Here is a key: $str_key\n";
            $unitsArray[$str_key] = $redis->hGetAll($str_key);
            // $this_array = $redis->hGetAll($str_key);
            // $this_array['id'] = $str_key;
            // array_push($unitsArray, $this_array);
        }
        //echo "No more keys to scan!\n";
    }

    echo "data: " . json_encode($unitsArray) . "\n\n";
    // $redis->get('SRV1:units');
    // $redis->get('SRV1:fps');
    // $mission = $redis->get('SRV1:mission:filename') . "." .$redis->get('SRV1:mission:island');
    // echo "data: {\"units\":" . $redis->get('SRV1:units') . ",\n";
    // echo "data: \"fps\":" . $redis->get('SRV1:fps') . ",\n";
    // echo "data: \"mission\": \"". $mission ."\"}\n\n";
    ob_flush();
    flush();
    sleep(3.1);
}

?>
