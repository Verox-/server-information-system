<?php

/* LICENCE
// Replay viewier.
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Leaflet JS test</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.css" />
        <link rel="stylesheet" href="./res/js/lib/leaflet.label/leaflet.label.css">
        <link rel="stylesheet" href="./res/css/replay.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" async>
        <link rel="stylesheet" href="./res/js/lib/SidebarV2/leaflet-sidebar.min.css" async></script>

        <script src="http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.js"></script>

        <script src="./res/js/lib/jsxcompressor/jsxcompressor.min.js"></script> <!--http://jsxgraph.uni-bayreuth.de/wp/2009/09/29/jsxcompressor-zlib-compressed-javascript-code/-->
        <script src="./res/js/lib/RotatedMarker/L.RotatedMarker.js" async></script>  <!--https://github.com/bbecquet/Leaflet.PolylineDecorator/blob/leaflet-0.7.2/src/L.RotatedMarker.js-->
        <script src="./res/js/lib/SidebarV2/leaflet-sidebar.min.js"></script>  <!--https://github.com/Turbo87/sidebar-v2-->
        <script src="./res/js/lib/leaflet.label/leaflet.label.js"></script>
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

            .sidebar:hover
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


    <div id="sidebarv2" class="sidebar collapsed">
        <!-- Nav tabs -->
        <div class="sidebar-tabs">
            <ul role="tablist">
                <li><a href="#aar" role="tab"><i class="fa fa-map"></i></a></li>
                <li><a href="#profile" role="tab"><i class="fa fa-male"></i></a></li>
                <li class="disabled"><a href="#messages" role="tab"><i class="fa fa-newspaper-o"></i></a></li>
            </ul>

            <ul role="tablist">
                <li><a href="#settings" role="tab"><i class="fa fa-gear"></i></a></li>
            </ul>
        </div>

        <!-- Tab panes -->
        <div class="sidebar-content">
            <div class="sidebar-pane" id="aar">
                <h1 class="sidebar-header">
                    United Operations' AAR
                    <div class="sidebar-close"><i class="fa fa-caret-left"></i></div>
                </h1>

                <p>Things can go here.</p>
            </div>

            <div class="sidebar-pane" id="profile">
                <h1 class="sidebar-header">Players<div class="sidebar-close"><i class="fa fa-caret-left"></i></div></h1>

                <div class="sideContainer blufor"> <!-- Side container -->
                    <h2><i class="fa fa-caret-right fa-1"></i> BLUFOR <i class="fa fa-caret-left fa-1"></i></h2>
                    <div class="groupsContainer"> <!-- Groups container -->
                    </div>
                </div>

                <div class="sideContainer redfor"> <!-- Side container -->
                    <h2><i class="fa fa-caret-down fa-1"></i> OPFOR <i class="fa fa-caret-down fa-1"></i></h2>
                    <div class="groupsContainer"> <!-- Groups container -->
                        <ul>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Alpha</h4>
                                <div>
                                    PlayerWithASuperFuckingLongName<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Bravo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Charlie</h4><br />
                                <div>
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Delta</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Echo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Foxtrot</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="sideContainer indfor"> <!-- Side container -->
                    <h2><i class="fa fa-caret-down fa-1"></i> INDEPENDANT <i class="fa fa-caret-down fa-1"></i></h2>
                    <div class="groupsContainer"> <!-- Groups container -->
                        <ul>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Alpha</h4>
                                <div>
                                    PlayerWithASuperFuckingLongName<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Bravo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Charlie</h4><br />
                                <div>
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Delta</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Echo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Foxtrot</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Alpha</h4>
                                <div>
                                    PlayerWithASuperFuckingLongName<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Bravo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Charlie</h4><br />
                                <div>
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Delta</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Echo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Foxtrot</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="sideContainer civ"> <!-- Side container -->
                    <h2><i class="fa fa-caret-down fa-1"></i> CIVILIAN <i class="fa fa-caret-down fa-1"></i></h2>
                    <div class="groupsContainer"> <!-- Groups container -->
                        <ul>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Alpha</h4>
                                <div>
                                    PlayerWithASuperFuckingLongName<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Bravo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Charlie</h4><br />
                                <div>
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Delta</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Echo</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                            <li class="indivGroupContainer"> <!-- Indivdual group -->
                                <h4>Foxtrot</h4><br />
                                <div>
                                    Player<br />
                                    Player<br />
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="sidebar-pane" id="messages">
                <h1 class="sidebar-header">Messages<div class="sidebar-close"><i class="fa fa-caret-left"></i></div></h1>
            </div>

            <div class="sidebar-pane" id="settings">
                <h1 class="sidebar-header">Settings<div class="sidebar-close"><i class="fa fa-caret-left"></i></div></h1>
            </div>
        </div>
    </div>

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
