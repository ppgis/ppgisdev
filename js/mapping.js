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

function myMap() {
    var mapCanvas = document.getElementById("map");
    var myCenter=new google.maps.LatLng(-27,153);
    //todo get bounds
    var mapOptions = {center: myCenter, zoom: 10, fullscreenControl: false};
    map = new google.maps.Map(mapCanvas, mapOptions);
    //todo have a loading warning
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
    //doesn't work if (staticmap) {}
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
        markerOptions['title'] = 'Double-click to remove',
            markerOptions['draggable'] = true
    }
    ;
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
}
function unhideele(side){
    bigele = document.getElementById(side+'big');
    smallele = document.getElementById(side+'small');
    bigele.style.display = 'block';
    smallele.style.display = 'none';
    if (side=='RHS') anotherline();
}
function anotherline() {
    var classname = 'rTC';
    var iconlist = document.getElementById('iconlist');
    iconlist.innerHTML = "";
    var markertoadd,n,j;
    allmarkers = makemarkerlist();//make var
    for (var iconID in allmarkers) {
        markertoadd = allmarkers[iconID];
        //how many of them?
        nlatlngs = markertoadd.lats.length;
        makeappendspantext(iconlist,nlatlngs,'rTCn');
        //add the images source//TODO cut this down
        var newdiv = document.createElement('span');
        newdiv.className=classname;
        var myImage = new Image(20, 20);
        myImage.src = markertoadd.src;
        newdiv.appendChild(myImage);
        iconlist.appendChild(newdiv);
        j = 0;
        makeappendspantext(iconlist,markertoadd.lats[j].toFixed(2), classname);
        makeappendtext(iconlist, ',', classname);
        makeappendspantext(iconlist,markertoadd.longs[j].toFixed(2), classname);
        for (j=1;j<nlatlngs;j++) {
            makeappend(iconlist,'br');
            //add some empty spans
            makeappendspantext(iconlist,' ', classname);
            makeappendspantext(iconlist,' ', classname);
            //add the number of icons
            makeappendspantext(iconlist,markertoadd.lats[j].toFixed(2), classname);
            makeappendtext(iconlist, ',', classname);
            makeappendspantext(iconlist,markertoadd.longs[j].toFixed(2), classname);
            //newele.setAttribute()
        }
        makeappend(iconlist,'br');
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
    document.getElementById('savetype').value = savetype;
    //alert('markers in form are: '+document.getElementById('markersjson').value);
    document.getElementById('markerForm').submit();
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
    }
    if (document.getElementById('RHSbig').style.display==='block') anotherline();
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