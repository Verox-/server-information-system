var currentIsland = undefined;
var map;
var replayDuration;
var mapInfo = new Array();

$("#mapContainer").append("<span class='consoleMessage'>Loading map controller...</span>");

function InitMapFromReplay(replayFirstFrame, replayLastFrame, replayFramesCount) {
    $("#mapContainer").append("<span class='consoleMessage'>Parsing meta frames...</span>");
    var replayMissionInfo = replayFirstFrame;
    var replayMissionDuration = replayLastFrame;
    replayDuration = replayMissionDuration.time;

    if (replayMissionInfo.mission == undefined) {
        // Get the info from the database.
        $("#mapContainer").append("<br /><span class='consoleErrorMessage'>Replay appears to be corrupted or in an invalid format: Unable to read meta information.</span><br />");
        return false;
    }
    var replayIsland = replayMissionInfo.mission.split(".");
    replayIsland = replayIsland[replayIsland.length - 1];
    if (!replayIsland.trim()) {
        $("#mapContainer").append("<br /><span class='consoleErrorMessage'>Replay appears to be corrupted or in an invalid format: Unable to determine island, blank or null.</span><br />");
    }
    $("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");

    if (map != undefined) {
        $("#mapContainer").append("<span class='consoleErrorMessage'>The replay viewer is in an error state: The map is already defined.</span><br />");
        return false;
    }

    $("#mapContainer").append("<span class='consoleMessage'>Initializing map...</span><br />");
    InitMap(replayIsland);

    return true;
}

function InitMap(island) {
    // Set the island var.
    currentIsland = island.toLowerCase();

    // Check if the map exists on the server.
    var jqxhr = $.getJSON(base_url + "/maps/" + island.toLowerCase() + "/map.json")
        .done(function(json) {
            // Kill the map container, just in case.
            $('#mapContainer').empty();

            // Create the container.
            $("#mapContainer").append("<div id='map'></div>");

            // Calculate the map center.
            var mapCenter = [json.latLngBounds[1][0] / 2, json.latLngBounds[1][1] / 2];

            // Create the map
            map = L.map('map', {
                crs: L.CRS.Simple,
                zoomControl: false
            }).setView(mapCenter, json.defaultZoom);

            // Set the tile layer.
            L.tileLayer(base_url + '/maps/{m}/{z}/{x}/{y}.png', {
                attribution: '<a target="_blank" href="/CREDITS">Credits</a>',
                minZoom: json.minZoom,
                maxZoom: json.maxZoom,
                tms: true,
                continuousWorld: true,
                m: island.toLowerCase(),
            }).addTo(map);

            // Extra shit.
            var popup = L.popup();
            //
            //  map.on('mousemove click', function(e) {
            //     window[e.type].innerHTML = 'GameCoord(' + LatLngToGameCoord(e.latlng) + '), ' + e.latlng.toString();
            //  });

            function onMapClick(e) {
                popup
                    .setLatLng(e.latlng)
                    .setContent(LatLngToGrid(e.latlng))
                    .openOn(map);
            }

            map.on('click', onMapClick);

            mapInfo['scaleFactor'] = json.scaleFactor;
            mapInfo['latOriginOffset'] = json.originOffset[0];
            mapInfo['lngOriginOffset'] = json.originOffset[1];
			mapInfo['mapSourceSize'] = json.mapSourceSize;
			mapInfo['gameXYBounds'] = json.gameXYBounds;
            console.info("The map successfully initialized.");

            UpdateUnitMarkers(frames[initialFramePointer].units, frames[initialFramePointer].groups);
            $("#replaySeeker").val(initialFramePointer);

            var sidebar_lf;
            sidebar_lf = L.control.sidebar('sidebarv2').addTo(map);
        })
        .fail(function() {
            console.log("FAILURE.");
            $("#mapContainer").show().append("<span class='consoleErrorMessage'>Unable to locate map definition for \"" + island.toLowerCase() + "\". Does this map exist in the tiles directory?</span>");
        });
}

function CalculateScaleFactor() {
    if (mapInfo['mapSourceSize'] == undefined) {
        console.error("\"mapSourceSize\" field in map definition (map.json) has not been set.");
        console.error("This is the size of the image in pixels, expressed as an array, and is required for calculation.");
        console.error("Example: \"mapSourceSize\": [26700,26700],");
        return false;
    }
    if (mapInfo['gameXYBounds'] == undefined) {
        console.error("\"gameXYBounds\" field in map definition (map.json) has not been set.");
        console.error("This is the bottom-left and top-right in-game coordinates of the map. This is required for scalefactor calculation.");
        console.error("\"gameXYBounds\": [[0,0],[5120,5120]],");
        return false;
    }
    var northEast = map.unproject([mapInfo['mapSourceSize'][0], mapInfo['mapSourceSize'][1]], map.getMaxZoom());
    var calcScaleFac = mapInfo['gameXYBounds'][1][0] / northEast.lng;
    console.info("Projected bounds: " + northEast.lng);
    console.info("Currently Defined Scale Factor: " + mapInfo['scaleFactor']);
    console.info("Calculated Scale Factor: " + calcScaleFac);
    console.warn("Above calculated scale factor is only valid for square maps.\nManual adjustment may be required if map X/Y are not equal.");
    return calcScaleFac;
}

function UnloadMap() {
    // Don't need to do anything.
    if (map == undefined) {
        return false;
    }

    // Unload the map.
    map.remove();

    // Kill the map container.
    $('#mapContainer').empty();

    // Delete the map variable.
    map = undefined;

    return true;
}
$("#mapContainer").append("<span class='consoleMessage'>done.</span><br />");
