<?php
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
$zd = gzopen($filename, "r");

if (isset($_GET['seek']) && $_GET['seek'] != 0)
{
    if (gzseek ($zd , $_GET['seek']) == -1) { die ("ERROR"); }
}
$contents = "";
$cLen = 0;
$mFsize = getMaxFileSize($filename);
while ($cLen < 10000000 && gztell($zd) < $mFsize)
{
    $contents .= stream_get_line($zd, 1000000, "\n") . "\n";
    $cLen = strlen($contents);
}

//gzread($zd, 997606);
$seeker = gztell ($zd);

if (gztell ($zd) >= $mFsize)
{
    $seeker = -1;
}

gzclose($zd);

$cData = compressData($contents);

function compressData($data)
{
    $compressedData = gzencode($data);

    return base64_encode($compressedData);
}

function getMaxFileSize($file)
{
    $fl = fopen($file, "r");
    fseek($fl, filesize($file) - 4);
    $buf = fread($fl, 4);
    return unpack("i", $buf)[1];
}
//header("Content-Type: application/json");
//header("Content-Encoding: gzip");

echo $seeker;
echo ":";
echo $cData;


 ?>
