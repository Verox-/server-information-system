<!DOCTYPE html>
<html>
<head>
<title>Static image example</title>
<script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ol3/3.6.0/ol.css" type="text/css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/ol3/3.6.0/ol.js"></script>
<style type="text/css">.pl-marker{
          width: 32px; height: 37px;
          background: url("http://res.cloudinary.com/bassist/image/upload/v1433987963/map-marker_mjyefn.png");
      }</style>
</head>
<body>
<div class="container-fluid">

<div class="row-fluid">
  <div class="span12">
    <div id="map" class="map"></div>
    <div class="pl-marker" id="pl1"></div>
  </div>
</div>

</div>
<script>
// Map views always need a projection.  Here we just want to map image
// coordinates directly to map coordinates, so we create a projection that uses
// the image extent in pixels.

var iconFeature = new ol.Feature({
  geometry: new ol.geom.Point([500, 500]),
  name: 'Null Island',
  population: 4000,
  rainfall: 500
});

var vectorSource = new ol.source.Vector({
  features: iconFeature //add an array of features
});

var iconStyle = new ol.style.Style({
  image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
    anchor: [0.5, 46],
    anchorXUnits: 'fraction',
    anchorYUnits: 'pixels',
    opacity: 0.75,
    src: 'marker.png'
  }))
});

var vectorLayer = new ol.layer.Vector({
  source: vectorSource,
  style: iconStyle
});

var extent = [0, 0, 8120, 8000];
var projection = new ol.proj.Projection({
  code: 'xkcd-image',
  units: 'pixels',
  extent: extent
});

var map = new ol.Map({
  layers: [
      vectorLayer,
    new ol.layer.Image({
      source: new ol.source.ImageStatic({
        attributions: [
          new ol.Attribution({
            html: '&copy; <a href="http://xkcd.com/license.html">xkcd</a>'
          })
        ],
        url: 'static_img.png',
        projection: projection,
        imageExtent: extent
      })
    })
  ],
  target: 'map',
  view: new ol.View({
    projection: projection,
    center: ol.extent.getCenter(extent),
    zoom: 2
  })
});
var lineString  = new ol.geom.LineString([]);
var path = [
    [500, 500],
    [600, 600],
    [700, 700],
    [800, 800]
];
var player1 = $('.pl1');

var marker = new ol.Overlay({
    positioning: 'center-center',
    offset: [0, 0],
    element: player1,
    stopEvent: false
});

map.addOverlay(marker);
lineString.setCoordinates(path);
map.once('postcompose', function(event) {
    interval = setInterval(animation, 500);
});

var i = 0, interval;
var animation = function(){

    if (i == path.length){
        i = 0;
    }

    marker.setPosition(path[i]);
    i++;
};

</script>
</body>
</html>
