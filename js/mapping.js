//to remove markers maybe just set their arraypos to -1
var allmarkers=[];
var allmarkerids=[];
var googlemarkers = [];//should really be an augmented object array integrated with allmarkers
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

function myMap() {
    var mapCanvas = document.getElementById("map");
    var myCenter=new google.maps.LatLng(-27,153);
    //todo get bounds
    var mapOptions = {center: myCenter, zoom: 10, fullscreenControl: false};
    map = new google.maps.Map(mapCanvas, mapOptions);
    //todo have a loading warning
    if (oldusericons != null){
        for (var i=0;i<oldusericons.length;i++){
            var tmplocation = new google.maps.LatLng(oldusericons[i].lat, oldusericons[i].lng);
            placeMarker(tmplocation,oldusericons[i].url,oldusericons[i].iconID);
        }
    }
    //google.maps.event.addListener(map, 'click', function(event) {
      //  placeMarker( event.latLng);
    //});
    //might need this if bounds checks stop working
     //google.maps.event.addListener(map, 'bounds_changed', function() {
     //   mapbounds = map.getBounds();
    //});
}

function placeMarker(location,theurl,theiconID) {
        //console.log("got one");
       // themarker.setPosition(location);
        if (arguments.length == 1){
            theanimation = google.maps.Animation.BOUNCE;
        }
        else {
            theanimation = null;
            currentmarker = theurl;
            currentID = theiconID;
        }
        nmarkers +=1;
        themarker = new google.maps.Marker({
            position: location,
            map: map,
            icon: {
                url: currentmarker
            },
            animation: theanimation,
            iconID: currentID,
            nmarker: nmarkers
        });
        googlemarkers.push(themarker);
        //check that it worked?
        //TODO no more than 40 of one marker type?
        setTimeout(function(){ themarker.setAnimation(null); }, 750);
        //document.getElementById('theform').style.display = 'block';
        google.maps.event.addListener(themarker, 'rightclick', function () {
            alert('Marker right clicked'+this.iconID);
        });
        //add currentid to list of ids that are placed
        markerstoredat = allmarkerids.indexOf(currentID);//use hasOwnProperty
        if (markerstoredat===-1) {
            allmarkerids.push(currentID);
            newmarkertype = {'type':currentID,'n': 1,'src':currentmarker,'lats':[location.lat()],
                'longs':[location.lng()],'nmarker':[nmarkers]};
            allmarkers.push(newmarkertype);
        }
        else {//retrieve and update
            oldmarkertype = allmarkers[markerstoredat];
            oldmarkertype.n += 1;
            oldmarkertype.lats.push(location.lat());
            oldmarkertype.longs.push(location.lng());
            oldmarkertype.nmarker.push(nmarkers);//ids of these markers
        }
        //allmarkers.push(themarker);
    //document.getElementById("latbox").innerHTML = location.lat().toFixed(6);
    //document.getElementById("lonbox").innerHTML = location.lng().toFixed(6);
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
    var markertoadd;
    for (i = 0; i < allmarkers.length; i++) {
        markertoadd = allmarkers[i];
        //TODO describe marker class
        //add the number of icons
        makeappendspantext(iconlist,markertoadd.n,'rTCn');
        //add the images source//TODO cut this down
        var newdiv = document.createElement('span');
        newdiv.className=classname;
        var myImage = new Image(20, 20);
        myImage.src = markertoadd.src;
        newdiv.appendChild(myImage);
        iconlist.appendChild(newdiv);
        var nlatlngs = markertoadd.lats.length;
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
function removezeros(){
    var zeros = [];
    for (i = 0; i < allmarkers.length; i++) {
        if ((themarker.n)==0) zeros.push(i);
    }
    for (i=0;i<zeros.length;i++){
        allmarkers.splice(zeros[i],1);
    }
}
function playjson(){
    //get rid of paths because they break the security settings
    //and remove the n=0 items if any
    alert (googlemarkers[0].iconID);

    /*
    removezeros();
    var themarker;
    for (i = 0; i < allmarkers.length; i++) {
        themarker = allmarkers[i];
        themarker.src = themarker.src.replace(/.*\//, "");
    }
    var markersjson = JSON.stringify(allmarkers);
    //alert('markers are: '+markersjson);
    document.getElementById('markersjson').value = markersjson;
    //alert('markers in form are: '+document.getElementById('markersjson').value);
    document.getElementById('markerForm').submit(); */
}

// Not used : When the user clicks on <div>, open the popup
function togglepopup() {
   alert("This is a HELP message. I may put it in a file perhaps? \n Or should I pop up another tab on the browser?");
}
function gethelp(){
    window.open("helpfile.pdf");
}


// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
    for (var i = 0; i < allmarkers.length; i++) {
        allmarkers[i].setMap(null);
    }
    allmarkers=[];
    allmarkerids=[];
    nmarkers = 0;
}