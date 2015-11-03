<?php
// if (empty($_GET['id']))
// {
//     die("Pass ID! replay.php?id=XXXXXXXXXXXXXXXXXXXXXXXXXXXXX");
// }

// Load the settings.
require_once __DIR__ . '/settings.php';
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
        <script src="./res/js/lib/jsxcompressor/jsxcompressor.min.js"></script> <!--http://jsxgraph.uni-bayreuth.de/wp/2009/09/29/jsxcompressor-zlib-compressed-javascript-code/-->
        <script src="./res/js/lib/RotatedMarker/L.RotatedMarker.js" async></script>  <!--https://github.com/bbecquet/Leaflet.PolylineDecorator/blob/leaflet-0.7.2/src/L.RotatedMarker.js-->
        <script src="./res/js/replay/mapControl.js" defer></script>
        <script src="./res/js/replay/markerControl.js" defer></script>
        <script src="./res/js/replay/replayControl.js" defer></script>

        <script type="text/javascript">
            var replay_base64 = "";
            var base_url = "<?=SIMRegistry::$settings['base_url']?>";
            var initialFramePointer = <?php echo (isset($_GET['frame']) && is_numeric($_GET['frame']) ? $_GET['frame'] : 0)?>;
            var replayIdentifierHash = "<?php echo $_GET['id']?>";
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
        <style>
            .controlsContainer {
                background:#fff;
                position:absolute;
                bottom:20px;
                left:5%;
                padding:5px;
                z-index:100;
                width: 90%;
                border-radius:3px;
                opacity: 0.4;
                display:flex;
                align-items:center;
            }

            .staticLinkContainer {
                background:#fff;
                position: absolute;
                bottom: 55px;
                left: 5%;
                padding: 2px;
                z-index: 100;
                border-radius: 3px;

                align-items: center;
            }

            .controlsContainer:hover
            {
                opacity: 1.0;
            }

            .abutton {
                appearance: button;
                -moz-appearance: button;
                -webkit-appearance: button;
                text-decoration: none; font: menu; color: ButtonText;
                display: inline-block; padding: 2px 8px;
            }

            .controlsContainer #staticLinkButton {
                margin: 0px 3px 0px 3px;
            }

            .controlsContainer #staticLinkButton:hover {
                color: green;
            }

            .controlsContainer #replaySeeker {
                margin: 0px 5px;
                flex-grow:1;
            }

            .controlsContainer #playPauseButton {
                margin: 0px 5px;
            }

            .replayTimeContainer {
                align-self: flex-end;
            }



        </style>
        <div id='controlsContainer' class='controlsContainer' style="display: none;">
            <i id="staticLinkButton" class="fa fa-link"></i>
            <input id="replaySeeker" style="min-width:75%" type ="range" min ="0" max="100" value ="1"/>
            <button id="playPauseButton"><i class='fa fa-play'></i></button>
            <div class="replayTimeContainer">
                <span id="dTime">-------</span>/<span id="tTime">-------</span>
            </div>
        </div>
        <div id='staticLinkContainer' class='staticLinkContainer' style="display: none;">
            <i id='staticLinkContainerClose' class="fa fa-times"></i>&nbsp;<span id='staticLinkText'>http://aar.unitedoperations.net/replay/D93A55C4A51AFF52E2E4BFED3BB28D89/frame/100</span>
        </div>
        <div id="mapContainer">



            <!--- <div id="map"></div> -->
            <div id="logContainer"></div>
        </div>
    </body>
</html>
