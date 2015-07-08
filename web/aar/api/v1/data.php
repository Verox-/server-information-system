<?php

// Define the master variable.
define('IN_SIM', true);

if ($_SERVER['REQUEST_METHOD'] != "POST")
    die ( "<h1>Invalid HTTP Method.</h1>" );

// Load the settings.
require_once __DIR__ . '/../../settings.php';

// Include the required class_uses
require_once "class/LiveCache.php";
require_once "class/RecordReplay.php";

// Get the raw contents of the POST data.
$json = file_get_contents("php://input"); // What the fuck, php...

// Decode the json.
$decoded_json = json_decode($json, true);
print_r($decoded_json);

// Init the live cache handler and perform the update.
$redisCache = new LiveCache();
$redisCache->Update($decoded_json);

$fileStore = new RecordReplay();
$fileStore->Record($decoded_json);



?>
