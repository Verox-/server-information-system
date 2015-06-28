<?php
$srvid = $_GET['server'];

// Set the headers.
header('Content-Type: text/event-stream');

$redis = new Redis();
$redis->connect('localhost');

while (true) {
    $unitsArray['filename'] = $redis->get($srvid . ":mission:filename");
    $unitsArray['island'] = $redis->get($srvid . ":mission:island");
    $unitsArray['playing'] = !empty($unitsArray['filename']);

    echo "data: " . json_encode($unitsArray) . "\n\n";
    // $redis->get('SRV1:units');
    // $redis->get('SRV1:fps');
    // $mission = $redis->get('SRV1:mission:filename') . "." .$redis->get('SRV1:mission:island');
    // echo "data: {\"units\":" . $redis->get('SRV1:units') . ",\n";
    // echo "data: \"fps\":" . $redis->get('SRV1:fps') . ",\n";
    // echo "data: \"mission\": \"". $mission ."\"}\n\n";
    ob_flush();
    flush();
    sleep(10);
}
 ?>
