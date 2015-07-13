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

// Clean up double-firing kills events.
/**
 * I'm not sure if the name field is sufficently unique to be using to filter duplicate events
 * Ideally, nid should be used as that is guarenteed unique by it's nature, but i'm unsure if
 * that will remain constant as the unit dies.
 */
if (count($decoded_json['data']['kills']) > 1) // If we only have 1 kill event then there's no need to clean double-fired events.
{
    $victims = array();

    foreach ($decoded_json['data']['kills'] as $k_key => $k_event)
    {
        if (in_array($k_event['victim']['name'],$victims))
        {
            unset($decoded_json['data']['kills'][$k_key]); // Trash the duplicate.
        }
        else
        {
            array_push($victims, $k_event['victim']['name']);
        }
    }
}

print_r($decoded_json);

// Init the live cache handler and perform the update.
$redisCache = new LiveCache();
$redisCache->Update($decoded_json);

$fileStore = new RecordReplay();
$fileStore->Record($decoded_json);



?>
