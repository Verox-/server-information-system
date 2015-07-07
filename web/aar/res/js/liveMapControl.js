var currentIsland = undefined;
var map;
var mapInfo = new Array();

var source = new EventSource("http://aar.unitedoperations.net/api/v1/MissionStatus.php?server=SRV1");
$("#mapContainer").append("<span style='color: grey'>No mission currently being played. The viewier will automatically activate when a mission starts.</span>");
source.onmessage = function(event) {
    var serverInfo = JSON.parse(event.data);

    if (!serverInfo.playing && map != undefined)
    {
        console.log(map);
        UnloadMap();
        $("#mapContainer").append("<span style='color: grey'>No mission currently being played. The viewier will automatically activate when a mission starts.</span>");
        return;
    }
    else if (!serverInfo.playing && map == undefined)
    {
        return;
    }
    else
    {
        if (serverInfo.island.toLowerCase() != currentIsland)
        {
            UnloadMap();
            console.log("NEW MAP!");
            InitMap(serverInfo.island);//
            //InitMap("Chernarus");
        }

        return;
    }
}

function InitMap(island)
{
    // Kill the map container, just in case.
    $('#mapContainer').empty();

    // Set the island var.
    currentIsland = island.toLowerCase();

    // Check if the map exists on the server.
    var jqxhr = $.getJSON( "http://aar.unitedoperations.net/maps/" + island.toLowerCase() + "/map.json" )
    .done(function( json ) {
        // Create the container.
        $("#mapContainer").append("<div id='map'></div>");

        // Calculate the map center.
        var mapCenter = [json.latLngBounds[1][0]/2,json.latLngBounds[1][1]/2];

        // Create the map
        map = L.map('map', {crs: L.CRS.Simple}).setView(mapCenter , json.defaultZoom);

        // Set the tile layer.
        L.tileLayer('http://aar.unitedoperations.net/maps/{m}/{z}/{x}/{y}.png', {
               attribution: '<a target="_blank" href="http://forums.bistudio.com/showthread.php?178671-Tiled-maps-Google-maps-compatible-%28WIP%29">Map data 10T</a>',
               minZoom: json.minZoom,
               maxZoom: json.maxZoom,
               tms: true,
               continuousWorld: true,
               m: island.toLowerCase(),
        }).addTo(map);

        // Extra shit.
        var popup = L.popup();
        //
        // map.on('mousemove click', function(e) {
        //    window[e.type].innerHTML = 'GameCoord(' + LatLngToGameCoord(e.latlng) + '), ' + e.latlng.toString();
        // });

        function onMapClick(e) {
            popup
               .setLatLng(e.latlng)
               .setContent(e.latlng.toString())
               .openOn(map);
        }

        map.on('click', onMapClick);

        mapInfo['scaleFactor'] = json.scaleFactor;
        mapInfo['latOriginOffset'] = json.originOffset[0];
        mapInfo['lngOriginOffset'] = json.originOffset[1];
        console.log(json.originOffset);
    })
    .fail(function() {
        console.log("FAILURE.");
        $("#mapContainer").append("<span style='color: red'>UNABLE TO LOCATE MAP DEFINITION FOR \"" + island.toLowerCase() + "\".<br \\>Does this map exist in the tiles directory?</span>");
    });
}

function UnloadMap()
{
    // Don't need to do anything.
    if (map == undefined)
    {
        return;
    }

    // Unload the map.
    map.remove();

    // Kill the map container.
    $('#mapContainer').empty();

    // Delete the map variable.
    map = undefined;
}
