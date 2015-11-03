<?php

/* LICENCE
// API endpoint for current mission status.
// Copyright (C) 2015 - Jerrad 'Verox' Murphy
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>. */

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
