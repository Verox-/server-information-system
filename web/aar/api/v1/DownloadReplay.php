<?php

// API Endpoint for serving chunked replays.
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
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

//ini_set('memory_limit', '300M');

if (!isset($_GET['id']))
{
    die ("No replay file specified.");
}

$filename = "/var/www/verox.me/public_html/aar/replays/{$_GET['id']}.replay";

// Check if the file exists here.
if (!file_exists($filename))
{
    die("ERROR");
}

// get contents of a gz-file into a string
try
{
    $zd = gzopen($filename, "r");
}
catch (Exception $ex)
{
    http_response_code(500);
    die("FATAL ERROR");
}

// Check if we need to seek to a specific point.
if (isset($_GET['seek']) && $_GET['seek'] != 0)
{
    if (gzseek ($zd , $_GET['seek']) == -1) { die ("ERROR"); }
}

$contents = "";
$cLen = 0;
$mFsize = getMaxFileSize($filename);
$lastPtr = 0;

// Loops until we have a maximum length reply or we run out of lines.
while ($cLen < 10000000 && gztell($zd) < $mFsize )
{
    // Grab the next 'few' bytes.
    $contents .= stream_get_line($zd, 1000000, "\n") . "\n";

    // Infinte loop protection on corrupted replays.
    // If a replay is corrupted somehow it could cause stream_get_line to get the
    // same few bytes over and over and over, this ensures this doesn't happen.
    if ($lastPtr == gztell($zd))
    {
        http_response_code(500);
        echo "INFINITE LOOP DETECTED!<br />";
        echo "IS THE REPLAY CORRUPTED?<br />";
        die("FATAL ERROR");
        break;
    }

    // Update the length and the current pointer.
    $cLen = strlen($contents);
    $lastPtr = gztell($zd);
}

// Set the current seek (-1 if end.)
$seeker = gztell ($zd);
if (gztell ($zd) >= $mFsize)
{
    $seeker = -1;
}

gzclose($zd);

// Recompresses and encodes data for transfer.
function compressData($data)
{
    return base64_encode(gzencode($data));
}

// Retrieves the uncompressed size of a gzipped file (last 4 bytes.)
function getMaxFileSize($file)
{
    $fl = fopen($file, "r");
    fseek($fl, filesize($file) - 4);
    $buf = fread($fl, 4);
    return unpack("i", $buf)[1];
}

echo $seeker;
echo ":";
echo compressData($contents);;
?>
