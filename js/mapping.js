//to remove markers maybe just set their arraypos to -1
var googlemarkers = {};
var nmarkers = 0;
var currentmarker;
var dx;
var dy;
var map;
// icon image anchors at mid bottom point
var xanchor = 10;
var yanchor = 31;
// hidden lhs and rhs divs
var lhshidden = true;
var rhshidden = true;
var ecx = 0;
var ecy = 0;
var currentID = 0;
var swbound;
var nebound;
var bclistener;
var staticmap = true;
var locationBar = false;
var locationBarVisible = false;
var roadBarVisible = false;
var havearoad = false;
//var buildingRoad = false;
var roadListener;
var endRoad;
var userroad = null;
var amlisteningtoroad = false;

function myMap() {
    var mapCanvas = document.getElementById("map");
    var myCenter=new google.maps.LatLng(-27,153);
    //todo get bounds
    var mapOptions = {
        center: myCenter,
        zoom: 13,
        fullscreenControl: false,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            position: google.maps.ControlPosition.TOP_LEFT
        },
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.LEFT_CENTER
        },
        streetViewControl: true,
        streetViewControlOptions: {
            position: google.maps.ControlPosition.LEFT_CENTER
        }
    };
    map = new google.maps.Map(mapCanvas, mapOptions);
    //TODO make the bounds reset work
    if (userroadpath != null){
        startRoad();
        userroad.setPath(userroadpath);
    }
    if (!staticmap) {
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('drawRoadPopup'));
        roadBarVisible = false;
        roadBar = document.getElementById('drawRoadPopup');
        roadBar.style.display = "none";
        if (haveBrisbaneLGA) addBrisbaneLayers();
    }
    //now for the markers
    if (oldusericons != null){
        var minlat = oldusericons[0].lat;
        var maxlat = oldusericons[0].lat;
        var minlong = oldusericons[0].lng;
        var maxlong = oldusericons[0].lng;
        for (var i=0;i<oldusericons.length;i++){
            thelat = oldusericons[i].lat;
            thelong = oldusericons[i].lng;
            var tmplocation = new google.maps.LatLng(oldusericons[i].lat, oldusericons[i].lng);
            placeMarker(tmplocation,oldusericons[i].url,oldusericons[i].iconID);
            if (thelat > maxlat) maxlat = thelat;
            if (thelat < minlat) minlat = thelat;
            if (thelong > maxlong) maxlong = thelong;
            if (thelong < minlong) minlong = thelong;
        }
        //make sure you can see those!
        swbound = new google.maps.LatLng(minlat,minlong);
        nebound = new google.maps.LatLng(maxlat,maxlong);
        bclistener = google.maps.event.addListener(map, 'bounds_changed', function() {
            // alert(swbound);
            mapbounds = map.getBounds();
            if ((!mapbounds.contains(swbound)) | (!mapbounds.contains(nebound))){
                mapbounds.extend(swbound);
                mapbounds.extend(nebound);
                map.fitBounds(mapbounds);
            }
            bclistener.remove();
        });

    }
    if (staticmap) {
        google.maps.event.addListenerOnce(map, "idle", function () {
            showmap();
        });
    }
    else {
        anotherline();
    }
    //doesn't work if (staticmap) {}
}

function addBrisbaneLayers(){
    //alert(haveBrisbaneLGA+" "+BrisbaneLGA);
    var KMLLayer = new google.maps.KmlLayer({
        url: BrisbaneLGA,//http://localhost/kml/BrisbaneLGA.kml',
        map: map
    });
}

function placeMarker(location,theurl,theiconID) {

    if (arguments.length == 1) {
        theanimation = google.maps.Animation.BOUNCE;
    }
    else {//placing old markers
        theanimation = null;
        currentmarker = theurl;
        currentID = theiconID;
    }
    nmarkers += 1;
    var markerOptions = {
        position: location,
        map: map,
        icon: {
            url: currentmarker
        },
        draggable: false,
        animation: theanimation,
        iconID: currentID,
        nmarker: nmarkers
    };
    if (!staticmap) {
        markerOptions['title'] = 'Double-click to remove';
        markerOptions['draggable'] = true;
    }
    var themarker = new google.maps.Marker(markerOptions);
    googlemarkers[nmarkers] = themarker;
    //check that it worked?
    //TODO no more than 40 of one marker type?
    setTimeout(function () {
        themarker.setAnimation(null);
    }, 750);
    //document.getElementById('theform').style.display = 'block';
    if (!staticmap){
        themarker.addListener('dblclick', function () {
            markerID = this.nmarker;
            googlemarkers[markerID].setMap(null);
            delete googlemarkers[markerID];
            if (document.getElementById('RHSbig').style.display == 'block') anotherline();
        });
        themarker.addListener('position_changed', function () {
            if (document.getElementById('RHSbig').style.display == 'block') anotherline();
        });
    }
}


function dropmarker(){
    //console.log("dropped");
    var rect = document.getElementById('map').getBoundingClientRect();
    //console.log(rect.left,ecx,rect.right,rect.top,ecy,rect.bottom,)
    var tol = 10;
    if ((ecx > (rect.left + tol) & (ecx < (rect.right - tol))) & (ecy > (rect.top+tol)) & (ecy < rect.bottom-tol)) canplace = true;
    else canplace = false;http://localhost/images/icons/icon1s.png
        //var xcoor = dx + e.clientX - rect.left;
        //var ycoor = dy + e.clientY - rect.top;
        var xcoor = dx + ecx - rect.left;
    var ycoor = dy + ecy - rect.top;
    var xc = ecx-rect.left;http://localhost/images/icons/icon6s.png
        var yc = ecy - rect.top;
    mouseLatLng = pixelToLatlng(xc,yc);
    markerLatLng = pixelToLatlng(xc+dx,yc+dy);
    if (isinmap(mouseLatLng)&canplace) {
        placeMarker(markerLatLng);
        if (document.getElementById('RHSbig').style.display=='block') anotherline();
    }

}



function findlocation(){
    if (roadBarVisible) {
        enableSave();
        hideRoadBar();
        if (amlisteningtoroad) finRoadlistener();
        if (havearoad) userroad.setEditable(false);
    }
    if (!locationBar) {//then initialise
       initLocationBar();
    }

    if (!locationBarVisible){
        showLocationBar();
    }
    else {
        hideLocationBar();
    }

}
function initLocationBar(){
    locationBar = true;

    var pac_input = document.getElementById('pac-input');
    pac_input.style.display = 'block';

    map.controls[google.maps.ControlPosition.TOP_CENTER].push(pac_input);


    autocomplete = new google.maps.places.Autocomplete(pac_input);
    setTimeout(function() {
        pac_input.focus();
    }, 500);

    autocomplete.bindTo('bounds', map);

    var targetImage = {
        url: '/images/icons/target.png',
        // This marker is 32 pixels wide by 32 pixels high.
        size: new google.maps.Size(32, 32),
        // The origin for this image is (0, 0).
        origin: new google.maps.Point(0, 0),
        // The anchor for this image is in the middle.
        anchor: new google.maps.Point(16,16)
    };


    pacmarker = new google.maps.Marker({
        map: map,
        icon: targetImage
    });
    autocomplete.addListener('place_changed', function () {

        var place = autocomplete.getPlace();
        if (!place.geometry) {
            return;
        }

        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        }

        // Set the position of the marker using the place ID and location.
        pacmarker.setPlace({
            placeId: place.place_id,
            location: place.geometry.location
        });
        pacmarker.setVisible(true);

    });
}
function hideLocationBar(){
    locationBarVisible = false;
    var pacinput = document.getElementById('pac-input');
    pacinput.style.display = 'none';
    pacinput.value = '';
    pacmarker.setVisible(false);
    document.getElementById('targeticon').title="Search for an address or place"
}
function showLocationBar(){
    locationBarVisible = true;
    var pacinput = document.getElementById('pac-input');
    pacinput.style.display = 'block';
    document.getElementById('targeticon').title="Hide Location Finder"
    pacinput.focus();
}

function disableSave(){
    var tmpsave = document.getElementById('tmpsave');
    tmpsave.className = 'dullbox';
    tmpsave.onclick = '';
    tmpsave.title = 'Save disabled during road construction';
    var finalsave = document.getElementById('finalsave');
    finalsave.className = 'dullbox';
    finalsave.onclick = '';
    finalsave.title = 'Save disabled during road construction';
}
function enableSave(){
    var tmpsave = document.getElementById('tmpsave');
    tmpsave.className = 'box';
    tmpsave.onclick = function(){submitjson('temp');};
    tmpsave.title = 'Save Map';
    var finalsave = document.getElementById('finalsave');
    finalsave.className = 'box';
    finalsave.onclick = function(){submitjson('final');};
    finalsave.title = "Finished: Save and Submit";
}

function drawRoad(){
    if (locationBarVisible) hideLocationBar();
    if (!havearoad){//initialise
        startRoad();
        startRoadlistener();
        //buildingRoad = true;
    }
    else {
        if (userroad.getPath().getLength()==0){
            //buildingRoad = true;
            startRoadlistener();
        }
        //else buildingRoad = false;
    }
    if (!roadBarVisible){
        disableSave();
        showRoadBar();
        if (havearoad) userroad.setEditable(true);
        //have already started the listener above
    }
    else {
        enableSave();
        hideRoadBar();
        if (amlisteningtoroad) finRoadlistener();
        if (havearoad) userroad.setEditable(false);
    }
}

function addLatLng(event) {
        var path = userroad.getPath();
        path.push(event.latLng);
        updateroadlist();
}

function finRoadlistener(){
    amlisteningtoroad = false;
        google.maps.event.removeListener(roadListener);
    document.getElementById('drawRoadPopuptext').innerText = 'Drag road vertices to reshape.';
    updateroadlist();
}
function startRoadlistener(){
    amlisteningtoroad = true;
    roadListener = map.addListener('click', addLatLng);
    document.getElementById('drawRoadPopuptext').innerText = 'Click to create points on the Road.';
}

function showroad(){
    console.log(userroad);
}

function restartRoad(){
    if (userroad != null)userroad.setPath([]);
    if (!amlisteningtoroad) startRoadlistener();
    if (document.getElementById('RHSbig').style.display==='block') updateroadlist();
}

function startRoad(){
    havearoad = true;
    userroad = new google.maps.Polyline ({
            strokeColor: '#ff0000',
            strokeOpacity: 0.6,
            strokeWeight: 2,
            clickable: true,
            zIndex: 1
    });
    userroad.setMap(map);
}
function hideRoadBar(){
    if (userroad !== null) {
        var len = userroad.getPath().getLength();
        if (len > 0) document.getElementById('roadicon').title = 'Edit or Redraw your road';
        else document.getElementById('roadicon').title = 'Add a road';
    }
    else document.getElementById('roadicon').title = 'Add a road';
    roadBarVisible = false;
    roadBar = document.getElementById('drawRoadPopup');
    roadBar.style.display="none";
    updateroadlist();
}
function showRoadBar(){
    document.getElementById('roadicon').title = 'Finish drawing';
    roadBarVisible = true;
    roadBar = document.getElementById('drawRoadPopup');
    roadBar.style.display="block";
    if (amlisteningtoroad) document.getElementById('drawRoadPopuptext').innerText = 'Click to create points on the Road.';
    else document.getElementById('drawRoadPopuptext').innerText = 'Drag road vertices to reshape.';
}

function makemarkerlist(){
    //add currentid to list of ids that are placed
    var allmarkers = {};
    var theiconID;
    for (var markernum in googlemarkers){
        themarker = googlemarkers[markernum];
        theiconID = themarker.iconID;
        if (allmarkers.hasOwnProperty(theiconID)){//already one
            allmarkers[theiconID].lats.push(themarker.position.lat());
            allmarkers[theiconID].longs.push(themarker.position.lng());
        }
        else { //new element
            newmarker = {'src':themarker.icon.url,'lats':[themarker.position.lat()],
                'longs':[themarker.position.lng()]};
            allmarkers[theiconID] = newmarker;
        }
    }
    return allmarkers;
}

//where am i
function getcoords(e){
    ecx = e.clientX;
    ecy = e.clientY;
}
function resetcoords(){
    //console.log("mouse out");
}

function setValue(){
    document.sampleForm.lat.value = document.getElementById("latbox").innerHTML;
    document.sampleForm.lon.value = document.getElementById("lonbox").innerHTML;
    document.forms["sampleForm"].submit();
}

function changeicon(e,ev){
    //debug alert(e.src);
    currentmarker = e.src;
    currentID = e.id;
    theid = e.id;
    rect = e.getBoundingClientRect();
    var leftpad = parseInt(window.getComputedStyle(e, null).getPropertyValue('padding-left'));
    var toppad = parseInt(window.getComputedStyle(e, null).getPropertyValue('padding-top'));
    //google maps anchors at mid bottom
    xanchor = 0.5*(e.clientWidth - 2*leftpad);//assuming equal padding
    yanchor = e.clientHeight - 2*toppad;
    //console.log(e.clientWidth,e.clientHeight,leftpad,toppad,xanchor,yanchor);
    dx = xanchor - (ev.clientX - rect.left - leftpad);
    dy = yanchor - (ev.clientY - rect.top - toppad);
}
var pixelToLatlng = function(xcoor, ycoor)
{
    //so far the getBounds has been working here
    //but you may need to add the event listener 'bounds_changed' (in mymap)
    var ne = map.getBounds().getNorthEast();
    var sw = map.getBounds().getSouthWest();
    projection = map.getProjection();
    topRight = projection.fromLatLngToPoint(ne);
    bottomLeft = projection.fromLatLngToPoint(sw);
    scale = 1 << map.getZoom();
    var newLatlng = projection.fromPointToLatLng(new google.maps.Point(xcoor / scale + bottomLeft.x, ycoor / scale + topRight.y));
    return newLatlng;
};
function isinmap(Location){
    //so far the getBounds has been working here
    //but you may need to add the event listener 'bounds_changed' (in mymap)
    return map.getBounds().contains(Location);
}
function changeit(e){
    var theclass = e.className;
    var allele = document.getElementsByClassName(theclass);
    console.log(allele.length);
    for (var i=0;i<allele.length;i++) {
        allele[i].style.display = "none";
    }
    allele = document.getElementsByClassName(theclass.toUpperCase());
    console.log(allele.length);
    for (var i=0;i<allele.length;i++) {
        allele[i].style.display = "none";
    }
}
function changeit2(side){
    blockele = document.getElementById(side.toUpperCase());
    arrowele = document.getElementById(side);
    if (side == 'lhs'){
        if (lhshidden){
            //unhide
            lhshidden = false;
            arrowele.src = 'arrowin.png';
            blockele.innerText = 'Some stuff';
        } else{
            //hide
            lhshidden = true;
            arrowele.src = 'arrowout.png';
            blockele.innerHTML = '';
        }
    }
    else {
        if (rhshidden) {
            rhshidden = false;
            arrowele.src = 'arrowout.png';
        } else {
            rhshidden = true;
            arrowele.src = 'arrowin.png';
        }
    }
}
function hideele(side){
    bigele = document.getElementById(side+'big');
    smallele = document.getElementById(side+'small');
    bigele.style.display = 'none';
    smallele.style.display = 'block';
    ex1ele = document.getElementsByClassName('ex1')[0];
    if (side=='RHS'){
        ex1ele.style.marginRight='30px';
    }
    else {
        ex1ele.style.marginLeft='30px';
    }
}
function unhideele(side){
    bigele = document.getElementById(side+'big');
    smallele = document.getElementById(side+'small');
    bigele.style.display = 'block';
    smallele.style.display = 'none';
    ex1ele = document.getElementsByClassName('ex1')[0];
    if (side=='RHS') {
        anotherline();
        ex1ele.style.marginRight='150px';
    }
    else {
        ex1ele.style.marginLeft='50px';
    }
}
function anotherline() {
    var classname = 'rTC';
    var iconlist = document.getElementById('iconlist');
    iconlist.innerHTML = "";
    var markertoadd,n,j;
    allmarkers = makemarkerlist();//make var
    var nomarkers = true;
        for (var iconID in allmarkers) {
            nomarkers = false;
            markertoadd = allmarkers[iconID];
            //how many of them?
            nlatlngs = markertoadd.lats.length;
            makeappendspantext(iconlist, nlatlngs, 'rTCn');
            //add the images source//TODO cut this down
            var newdiv = document.createElement('span');
            newdiv.className = classname;
            var myImage = new Image(20, 20);
            myImage.src = markertoadd.src;
            newdiv.appendChild(myImage);
            iconlist.appendChild(newdiv);
            j = 0;
            makeappendspantext(iconlist, markertoadd.lats[j].toFixed(3), classname);
            makeappendtext(iconlist, ',', classname);
            makeappendspantext(iconlist, markertoadd.longs[j].toFixed(3), classname);
            for (j = 1; j < nlatlngs; j++) {
                makeappend(iconlist, 'br');
                //add some empty spans
                makeappendspantext(iconlist, ' ', classname);
                makeappendspantext(iconlist, ' ', classname);
                //add the number of icons
                makeappendspantext(iconlist, markertoadd.lats[j].toFixed(3), classname);
                makeappendtext(iconlist, ',', classname);
                makeappendspantext(iconlist, markertoadd.longs[j].toFixed(3), classname);
                //newele.setAttribute()
            }
            makeappend(iconlist, 'br');
        }
        if (nomarkers) makeappendspantext(iconlist, 'No markers', 'rTCNone');
    //now for the road
    updateroadlist();
}
function updateroadlist(){
    roadlist = document.getElementById('roadlist');
    if (userroad != null){
        var roadlen = userroad.getPath().getLength();
        if (roadlen == 0) roadlist.innerText = "No road";
        else if (roadlen == 1) roadlist.innerText = "1 Point";
        else roadlist.innerText = roadlen+" Points";
    }
    else {
        roadlist.innerText = "No road";
    }
}

function makeappend(theparent,childtype){
    var newele = document.createElement(childtype);
    theparent.appendChild(newele);
}
function makeappendspantext(theparent,childcontents,classname){
    var newdiv = document.createElement('span');
    newdiv.className=classname;
    var newele = document.createTextNode(childcontents);
    newdiv.appendChild(newele);
    theparent.appendChild(newdiv);
}
function makeappendtext(theparent,childcontents){
    var newchild = document.createTextNode(childcontents);
    theparent.appendChild(newchild);
}
function submitjson(savetype){
    //get rid of paths because they break the security settings
    var themarker,allmarkers = makemarkerlist();
    //console.log(allmarkers);
    for (var theiconID in allmarkers) {
        themarker = allmarkers[theiconID];
        themarker.src = themarker.src.replace(/.*\//, "");
    }
    var markersjson = JSON.stringify(allmarkers);
    //alert('markers are: '+markersjson);
    document.getElementById('markersjson').value = markersjson;
    if (userroad != null){
        var roadjson = getroadJSON();
        document.getElementById('roadjson').value = roadjson;
    }
    document.getElementById('savetype').value = savetype;
    //alert('markers in form are: '+document.getElementById('markersjson').value);
    document.getElementById('markerForm').submit();
}

function getroadJSON(){
    var roadLatLngs = userroad.getPath().getArray();
    var tmparr = [];
    for (var i =0;i<userroad.getPath().getLength();i++){
        tmparr[i] = [roadLatLngs[i].lat(),roadLatLngs[i].lng()];
    }
    return JSON.stringify(tmparr);
}

// Not used : When the user clicks on <div>, open the popup
function togglepopup() {
    alert("This is a HELP message. I may put it in a file perhaps? \n Or should I pop up another tab on the browser?");
}
function gethelp(){
    window.open("helpfile.pdf");
}


// Deletes all markers in the array by removing references to them.
function removeall() {
    for (var markerID in googlemarkers){
        googlemarkers[markerID].setMap(null);
        delete googlemarkers[markerID];
        //nmarkers = 0;
    }
    if (document.getElementById('RHSbig').style.display==='block') anotherline();
    restartRoad();
}
var largemap = true;
function checkitout(e){
    var theotherid = e.id.replace('dummy','other');
    var theother = document.getElementById(theotherid);
    if (e.checked == false ){theother.value = '';theother.placeholder = 'Other (please specify)'}
    else {theother.placeholder = 'Other (please specify)';}
}
function showmap(){
    var themap = document.getElementById('map');
    var theheight = themap.style.innerHeight;
    if (largemap){themap.style.height = '40px';largemap = false;}
    else {themap.style.height = '400px';largemap = true;}
}
