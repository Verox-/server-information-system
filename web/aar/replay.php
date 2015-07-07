<?php
if (empty($_GET['id']))
{
    die("Pass ID. Replay is embedded for now.<br />js_decompres_test.php?id=XXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
}

function jxgcompress($filename)
{
    if (file_exists($filename)) {
        /*$base64 =*/ return base64_encode(file_get_contents($filename));
        //echo "<script>var jxgcompressed = \"$base64\";</script>\n";
    } else {
        throw new Exception("$filename not found");
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Leaflet JS test</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css" />
        <script src="http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js"></script>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" async>
        <script src="/res/js/replay/mapControl.js" defer></script>
        <script src="/res/js/replay/markerControl.js" defer></script>
        <script src="/res/js/replay/replayControl.js" defer></script>
        <script src="/res/js/lib/jsxcompressor/jsxcompressor.min.js"></script> <!--http://jsxgraph.uni-bayreuth.de/wp/2009/09/29/jsxcompressor-zlib-compressed-javascript-code/-->
        <script src="/res/js/lib/RotatedMarker/L.RotatedMarker.js"></script>  <!--https://github.com/bbecquet/Leaflet.PolylineDecorator/blob/leaflet-0.7.2/src/L.RotatedMarker.js-->
        <script type="text/javascript">
            var replay_base64 = "<?php try {echo jxgcompress("./replays/{$_GET['id']}.replay");} catch (Exception $ex) {echo "ERROR";} ?>";
        </script>
        <style>
            html, body, #map, #mapContainer {
               height:100%;
               width:100%;
               padding:0px;
               margin:0px;
               background:#000;
               font-family: "Lucida Console", Courier, monospace;
            }

            .consoleMessage {
                color: grey;
            }

            .consoleErrorMessage {
                color: darkred;
                font-weight: bolder;
            }

            .consoleWarnMessage {
                color: yellow;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <style>.</style>
    <style>
.seekerContainer {
background:#fff;
position:absolute;
bottom:20px;
left:5%;
padding:5px;
z-index:100;
width: 90%;
border-radius:3px;
}

.abutton {
    appearance: button;
    -moz-appearance: button;
    -webkit-appearance: button;
    text-decoration: none; font: menu; color: ButtonText;
    display: inline-block; padding: 2px 8px;
}
</style>
<div id='output' id='seekerContainer' class='seekerContainer' style="display: none;">
    <input style="width:80%" id="replaySeeker" type ="range" min ="0" max="100" value ="1"/>
    <span id="dTime">INITIALZING</span>
    <a id="stopButton" class="abutton" value ="" disabled><i class='fa fa-stop'></i></a>
    <a id="playButton" class="abutton" value ="" disabled><i class='fa fa-play'></i></a>
</div>
        <div id="mapContainer">



            <!--- <div id="map"></div> -->
            <div id="logContainer"></div>
        </div>
    </body>
</html>
