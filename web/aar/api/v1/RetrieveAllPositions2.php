<?php
header('Content-Type: text/event-stream');

$redis = new Redis();
$redis->connect('localhost');
$redis->select(1);
    $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY); /* retry when we get no keys back */
//echo "Doing it!";


while (true) {
    $inMission = true;


    // while ($inMission)
    // {
    //     $mission = $redis->get('SRV1:mission:filename') . "." .$redis->get('SRV1:mission:island');
    // }

    $unitsArray = array();
    $it = NULL; /* Initialize our iterator to NULL */

    while($arr_keys = $redis->scan($it)) {
        foreach($arr_keys as $str_key) {
            $unitsArray[$str_key] = $redis->hGetAll($str_key);
        }
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
