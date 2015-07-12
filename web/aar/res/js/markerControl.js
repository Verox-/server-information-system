
var markers = new Array();

var source = new EventSource("http://aar.unitedoperations.net/api/v1/RetrieveAllPositions.php");
source.onmessage = function(event) {

    if (map == undefined)
    {
        return;
    }

   var units = JSON.parse(event.data);
   UpdateUnitMarkers(units);


}



function UpdateUnitMarkers(units)
{
    for (var key in units)
    {
        units[key].pos = GameCoordToLatLng(units[key].pos);

        if (!(key in markers))
        {
            var sideColor = GetSideColor(units[key].fac, units[key].uid);

            var popupTitle = "Player: " + units[key].uid + "<br />Waiting for update";
            if (units[key].uid == "")
            {
                popupTitle = "AI<br />Waiting for Update.";
            }

            // Add the markah.
            markers[key] = L.circle(units[key].pos, 500,
            {
                color: sideColor,
            }).addTo(map);


            markers[key].bindPopup("<b>" + popupTitle + "</b>");
            console.log("new");
        }
        else
        {
            // Update the markah.
            markers[key].setLatLng(units[key].pos);
            //markers[key].update(); // DOn't need this for circles
            //markers[key].setStyle({color: GetSideColor(units[key].fac, units[key].uid)})
            //
            //
            if (units[key].uid == "") {
                units[key].name = units[key].name + " (AI)"
            }
            // Update the CPOS.
            markers[key].bindPopup("<h3>" + units[key].name + "</h3>UID: " + units[key].uid + "<br />MPOS: " + markers[key].getLatLng().toString() + "<br />GPOS: GameCoord(" + LatLngToGameCoord(markers[key].getLatLng()) + ")<br />FAC: " +  units[key].fac + "<br />DIR: " +  units[key].dir)
            console.log("update");
        }
    }

    for (var key in markers)
    {
        if (!(key in units))
        {
            map.removeLayer(markers[key]);
            delete markers[key];
            console.log("remove");
        }
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
                return "blue";
            }
            else
            {
                return "cyan";
            }

            break;
        case "EAST":
            if (player == "")
            {
                return "red";
            }
            else
            {
                return "pink";
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


//// --- Utility functions to convert coords --- ////
function LatLngToGameCoord(ltln)
{
   var coord_x = (ltln.lng + 0.58593) * mapInfo.scaleFactor;
   var coord_y = (ltln.lat + 43.5702981355) * mapInfo.scaleFactor;
   coord_x = coord_x.toFixed(2);
   coord_y = coord_y.toFixed(2);
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

function GameCoordToLatLng(coord)
{
   var coord_array  = JSON.parse(coord); // For fuck sake.

   // This is placeholder code.
   var coord_x = (coord_array[0] / mapInfo.scaleFactor) - mapInfo.latOriginOffset;
   var coord_y = (coord_array[1] / mapInfo.scaleFactor) - mapInfo.lngOriginOffset;

   // This isn't.
   return [coord_y, coord_x]; // X-Y LNG-LAT... fucking confusing...
}
