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
    $tmp_kills = array();

    // Loop every kill event.
    foreach ($decoded_json['data']['kills'] as $k_key => $k_event)
    {
        // If an event with the victim already exists
        if (isset($tmp_kills[$k_event['victim']['nid']]))
        {
            // Check if the victim and the killer are the same person, if they aren't this is not the duplicate and replace the existing event.
            if ($k_event['victim']['nid'] != $k_event['killer']['nid'])
            {
                $tmp_kills[$k_event['victim']['nid']] = $k_event;
            }
            // If they are the same, this is the duplicate event and we can disregard it.
        }
        else
        {
            // No event already exists, add this event to the temp array.
            $tmp_kills[$k_event['victim']['nid']] = $k_event;
        }
    }

    // Replace the original array with the fixed array.
    $decoded_json['data']['kills'] = $tmp_kills;
}

print_r($decoded_json);

// Init the live cache handler and perform the update.
$redisCache = new LiveCache();
$redisCache->Update($decoded_json);

$fileStore = new RecordReplay();
$fileStore->Record($decoded_json);



?>
