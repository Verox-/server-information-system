
var markers = new Array();

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


//// --- Utility functions to convert coords --- ////
function LatLngToGameCoord(ltln)
{
   var coord_x = (ltln.lng + mapInfo.latOriginOffset) * mapInfo.scaleFactor;
   var coord_y = (ltln.lat + mapInfo.lngOriginOffset) * mapInfo.scaleFactor;

   return [coord_y, coord_x];
}

function LatLngToGrid(ltln)
{
   var coord_x = Math.round((ltln.lng + mapInfo.latOriginOffset) * mapInfo.scaleFactor);
   var coord_y = (ltln.lat + mapInfo.lngOriginOffset) * mapInfo.scaleFactor;

   coord_x = coord_x.toString().substr(1, 4);
   //coord_y = num.toPrecision(4);

   return [coord_y, coord_x];
}

function GameCoordToGrid(coord)
{
    function pad(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    var coord_array  = JSON.parse(coord);
    var northing = Math.floor(coord_array[0] / 10);
    var easting = Math.floor(coord_array[1] / 10);

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
