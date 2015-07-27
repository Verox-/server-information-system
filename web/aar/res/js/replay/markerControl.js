
var markers = new Array();
var kills = new Array();
/**
 * Give me JSON formatted unit data.
 */
function UpdateUnitMarkers(units)
{
    var markerRemoveQueue = Object.keys(markers);

    for (var key in units)
    {
        units[key].latlng = GameCoordToLatLng(units[key].pos);
        var uNid = units[key].nid;
        if (markers[uNid] == null)
        {
            var sideColor = GetSideColor(units[key].fac, units[key].uid);

            var svgURL = "data:image/svg+xml;base64," + btoa(GetSideIcon(units[key].fac, "unit"));
            var popupTitle = "Player: " + units[key].uid + "<br />Waiting for update";
            if (units[key].uid == "")
            {
                popupTitle = "AI<br />Waiting for Update.";
            }

            // Add the markah.
            // markers[key] = L.circle(units[key].pos, 500,
            // {
            //     color: sideColor,
            // }).addTo(map);

            // create icon
            var mySVGIcon = L.icon( {
                iconUrl: svgURL,
                iconSize: [15, 15],
                shadowSize: [12, 10],
                iconAnchor: [5, 5],
                popupAnchor: [2, -4]
            } );

            markers[uNid] = L.rotatedMarker( units[key].latlng, { icon: mySVGIcon, angle:units[key].dir} ).addTo(map);

            markers[uNid].bindPopup("<b>" + popupTitle + "</b>");
            console.log("new");
        }
        else
        {
            // Update the markah.
            markers[uNid].setLatLng(units[key].latlng); //https://groups.google.com/forum/#!topic/leaflet-js/GSisdUm5rEc
            markers[uNid].update(); // DOn't need this for circles
            markers[uNid].setAngle(units[key].dir);
            //markers[key].setStyle({color: GetSideColor(units[key].fac, units[key].uid)})

            // Remove this marker from the remove queue.
            var mrqId = markerRemoveQueue.indexOf(uNid);
            if (mrqId != -1 )
            {
                markerRemoveQueue.splice(mrqId, 1);
            }

            var unitName;
            if (units[key].uid == "") {
                unitName = units[key].name + " (AI)";
            }
            else
            {
                unitName = units[key].name;
            }

            var popupContent = "<h3>" + unitName + "</h3>UID: " + units[key].uid + "<br />MPOS: " + markers[uNid].getLatLng().toString() + "<br />CPOS: GameCoord(" + units[key].pos + ")<br />GRID: Grid(" + GameCoordToGrid(units[key].pos) + ")<br />FAC: " +  units[key].fac + "<br />DIR: " +  units[key].dir

            if (units[key].uid == "") {
                units[key].name = units[key].name + " (AI)"
            }

            // Update the CPOS.
            markers[uNid].getPopup().setContent(popupContent).update();
            console.log("update");
        }
    }

    for (var key in markerRemoveQueue)
    {
        if (key == undefined)
        {
            continue;
        }

        map.removeLayer(markers[markerRemoveQueue[key]]);
        delete markers[markerRemoveQueue[key]];
        //markers[markerRemoveQueue[key]] = undefined;
        console.log("remove");
    }
    //console.log(units);
}

/**
 * Give me JSON formatted unit data.
 */
function HandleKillEvents(killEvent)
{
    for (var key in killEvent)
    {
        // Calculate the victim's position.
        killEvent[key].latlng = GameCoordToLatLng(killEvent[key].victim.pos);
        var killMarkerSvg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="Layer_1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve"><line fill="none" stroke="#000000" stroke-width="1.5" stroke-miterlimit="10" x1="3.171" y1="8.828" x2="8.828" y2="3.171"/><line fill="none" stroke="#000000" stroke-width="1.5" stroke-miterlimit="10" x1="3.171" y1="3.171" x2="8.828" y2="8.828"/></svg>';
        var svgURL = "data:image/svg+xml;base64," + btoa(killMarkerSvg);

        if (killEvent[key].killer.name != killEvent[key].victim.name)
        {
            var popupText = "<b>" + killEvent[key].killer.name + "(" + killEvent[key].killer.fac + ") killed "+ killEvent[key].victim.name+ "(" + killEvent[key].victim.fac + ")</b><br />";
            popupText = popupText + "Distance: " + CalcCoord2DDistance(killEvent[key].killer.pos, killEvent[key].victim.pos) + "m<br />";
            popupText = popupText + "Mission Time: " + TimeStringify(killEvent[key].time,3600)+ "<br /><br />";
            popupText = popupText + "<button><i class='fa fa-credit-card'></i>&nbsp;Go Premium to see more!</button>";
        }
        else
        {
            var popupText = killEvent[key].victim.name + "(" + killEvent[key].victim.fac + ") killed himself";
        }

        // Construct the marker.
        var mySVGIcon = L.icon( {
            iconUrl: svgURL,
            iconSize: [12, 12],
            shadowSize: [12, 10],
            iconAnchor: [5, 5],
            popupAnchor: [2, -4]
        } );

        // Add the marker to the map and bind the popup.
        var tmp_killMarker = L.rotatedMarker( killEvent[key].latlng, { icon: mySVGIcon, angle:0} ).addTo(map);
        var tmp_boundPopup = tmp_killMarker.bindPopup(popupText);

        var tmp_linespos = [0,0];

        if (killEvent[key].killer.name != killEvent[key].victim.name)
        {
            tmp_linespos = [GameCoordToLatLng(killEvent[key].victim.pos),GameCoordToLatLng(killEvent[key].killer.pos)];
        }

        var tmp_killPopupLine = L.polyline(tmp_linespos, {color: 'red', opacity: 0.8, weight: 2, clickable: false});

        tmp_boundPopup.on('popupopen', function(e) {
            tmp_killPopupLine.addTo(map);
        });
        tmp_boundPopup.on('popupclose', function(e) {
            map.removeLayer(tmp_killPopupLine);
        });

        // Push the marker to the array.
        kills.push(tmp_killMarker);

        console.log("kill");
    }

    function TimeStringify(sec, totalsec) {
        function pad(n, width, z) {
            z = z || '0';
            n = n + '';
            return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
        }

        var hours = Math.floor(sec / 3600);
        sec = sec - (hours * 3600); //why didn't mod work...
        var minutes = Math.floor(sec / 60);
        var seconds = Math.floor(sec % 60);

        var result;
        var result = (totalsec >= 3600 ? pad(hours, 2) + "h " : "");
        result = result + (minutes != 0 ? pad(minutes, 2) + "m " : "00m ");
        result = result + (seconds != 0 ? pad(seconds, 2) + "s" : "00s");

        return result;
    }
}



//// --- Utility functions to convert coords, ect --- ////

function CalcCoord2DDistance(coord1, coord2)
{
    if (typeof coord1 === "string")
    {
        // Assume they're both json.
        coord1 = JSON.parse(coord1);
        coord2 = JSON.parse(coord2);
    }

    dX = Math.abs(coord1[0] - coord2[0]);
    dY = Math.abs(coord1[1] - coord2[1]);

    return Math.round(Math.sqrt((dX * dX) + (dY * dY)));
}

function GetSideColor(side, player)
{
    switch (side)
    {
        case "WEST":
            if (player == "")
            {
                return "darkblue";
            }
            else
            {
                return "blue";
            }

            break;
        case "EAST":
            if (player == "")
            {
                return "darkred";
            }
            else
            {
                return "red";
            }
            break;
        case "GUER":
            return "lime";
            break;
        case "CIV":
            return "purple";
            break;
        default:
            return "black";
            break;

    }
}

function GetSideIcon(side, player)
{
    switch (side)
    {
        case "WEST":
            return '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="Layer_1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve"><polygon points="4.515 3.406 6 0.297 7.484 3.406 "/><circle fill="none" stroke="blue" stroke-miterlimit="10" cx="6" cy="6" r="2.5"/></svg>';
            break;
        case "EAST":
            return '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="Layer_1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve"><polygon points="4.515 3.406 6 0.297 7.484 3.406 "/><circle fill="none" stroke="red" stroke-miterlimit="10" cx="6" cy="6" r="2.5"/></svg>';
            break;
        case "GUER":
            return '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="Layer_1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve"><polygon points="4.515 3.406 6 0.297 7.484 3.406 "/><circle fill="none" stroke="lime" stroke-miterlimit="10" cx="6" cy="6" r="2.5"/></svg>';
            break;
        case "CIV":
            return '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="Layer_1" x="0px" y="0px" width="12px" height="12px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve"><polygon points="4.515 3.406 6 0.297 7.484 3.406 "/><circle fill="none" stroke="purple" stroke-miterlimit="10" cx="6" cy="6" r="2.5"/></svg>';
            break;
        default:
            return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="10px" height="10px" viewBox="0 0 10 10" xml:space="preserve"><style>.style0{stroke:	black;stroke-miterlimit:	10;fill:	none;}</style><circle cx="5.021" cy="4.979" r="4.031" class="style0"/><polygon points="3.584,4.297 5,1.844 6.416,4.297"/></svg>';
            break;

    }
}

function LatLngToGameCoord(ltln)
{
   var coord_x = (ltln.lng + mapInfo.latOriginOffset) * mapInfo.scaleFactor;
   var coord_y = (ltln.lat + mapInfo.lngOriginOffset) * mapInfo.scaleFactor;

   return [coord_y, coord_x];
}

function LatLngToGrid(ltln)
{
    var gameCoords = LatLngToGameCoord(ltln);
    return GameCoordToGrid(gameCoords);
   //var coord_x = Math.round((ltln.lng + mapInfo.latOriginOffset) * mapInfo.scaleFactor);
   //var coord_y = (ltln.lat + mapInfo.lngOriginOffset) * mapInfo.scaleFactor;

   //coord_x = coord_x.toString().substr(1, 4);
   //coord_y = num.toPrecision(4);

   //return [coord_y, coord_x];
}

function GameCoordToGrid(coord)
{
    function pad(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    if (typeof coord === "string")
    {
        // Assume they're both json.
        coord = JSON.parse(coord);
    }

    var northing = Math.floor(coord[0] / 10);
    var easting = Math.floor(coord[1] / 10);

    northing = pad(northing, 4);
    easting = pad(easting, 4);

    return northing + easting;
}

function GameCoordToLatLng(coord)
{
   var coord_array  = JSON.parse(coord); // For fuck sake.

   var coord_x = (coord_array[0] / mapInfo.scaleFactor) - mapInfo.latOriginOffset;
   var coord_y = (coord_array[1] / mapInfo.scaleFactor) - mapInfo.lngOriginOffset;

   // This isn't.
   return [coord_y, coord_x]; // X-Y LNG-LAT... fucking confusing...
}
