
var themarker;
var currentmarker;
var dx;
var dy;
var map;
var scale;
var bottomLeft;
var topRight;
var projection;

function myMap() {
    var mapCanvas = document.getElementById("map");
    var myCenter=new google.maps.LatLng(-27,153);
    var mapOptions = {center: myCenter, zoom: 8, fullscreenControl: false};
    map = new google.maps.Map(mapCanvas, mapOptions);
    google.maps.event.addListener(map, 'click', function(event) {
        placeMarker( event.latLng);
    });
    google.maps.event.addListener(map, 'bounds_changed', function() {
        //alert(map.getBounds());
        var ne = map.getBounds().getNorthEast();
        var sw = map.getBounds().getSouthWest();
        projection = map.getProjection();
        topRight = projection.fromLatLngToPoint(ne);
        bottomLeft = projection.fromLatLngToPoint(sw);
        scale = 1 << map.getZoom();
        //console.log(scale);
    });
}

function placeMarker( location) {
    //console.log(anchorpoint);
    if (themarker){
        themarker.setPosition(location);
    } else {

            themarker = new google.maps.Marker({
                position: location,
                map: map,
                icon: {
                    url: currentmarker
                }
            });

        document.getElementById('theform').style.display = 'block';
    google.maps.event.addListener(themarker, 'rightclick', function () {
        alert('Marker right clicked');
    });
    }
    document.getElementById("latbox").innerHTML = location.lat().toFixed(6);
    document.getElementById("lonbox").innerHTML = location.lng().toFixed(6);
}


function dropmarker(e,ele){
    //console.log(e.clientX+" "+e.clientY);
    var rect = document.getElementById('map').getBoundingClientRect();
    //console.log(rect.top.toFixed(0), rect.right, rect.bottom, rect.left.toFixed(0));
    var xcoor = dx + e.clientX - rect.left;
    var ycoor = dy + e.clientY - rect.top;
    newLatLng = pixelToLatlng(xcoor,ycoor);
    placeMarker(newLatLng);
}

function setValue(){
    document.sampleForm.lat.value = document.getElementById("latbox").innerHTML;
    document.sampleForm.lon.value = document.getElementById("lonbox").innerHTML;
    document.forms["sampleForm"].submit();
}

function changeicon(e,ev){
    //debug alert(e.src);
    currentmarker = e.src;
    var theicon = document.getElementById('cornericon');
    theicon.style.visibility='visible';
    theicon.src = e.src;
    rect = document.getElementById('icon1').getBoundingClientRect();
    dx = 10 - (ev.clientX - rect.left - 2);//10 is the x anchor and 2 is the left padding
    dy = 31 - (ev.clientY - rect.top - 5);//31 is the y anchor and 5 is the top padding
}
var pixelToLatlng = function(xcoor, ycoor)
{
    var newLatlng = projection.fromPointToLatLng(new google.maps.Point(xcoor / scale + bottomLeft.x, ycoor / scale + topRight.y));
    return newLatlng;
};