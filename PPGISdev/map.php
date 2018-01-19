<?php
//TODO free mysqli stuff and close connections
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "mappingfns.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$pagetitle = "mapping";

$testforguest = $config['testforguest'];
$loggedin = false;

$backfromsave = false;
if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['message']))){
    if (test_input($_GET['message'])=='success') $backfromsave = true;
}

$errorMessage = "";//will display on page
$msgtype='bad';
$message = "";//will display on error page. anything in here will send us to the error page

$activepage = test_input($_SERVER['SCRIPT_NAME']);
//get rid of trailing stuff
$activepage = preg_replace('/.php.*/','.php',$activepage);
//get rid of leading stuff
$activepage = preg_replace('/^.*\//','',$activepage);

session_start();

$_SESSION['comebackto'] = $activepage;
//were to go back to after login if required?
$gotonext =  $_SESSION['comebackto'];
$phpgotonext = "Location: ".$gotonext;

$loggedin = false;

//already have a session?
if (!empty($_SESSION['sessionuname'])){
    //this will automatically make the user confirm that they want to continue
    $sessionuname = $_SESSION['sessionuname'];
    $msgtype='nice';
    $loggedin = true;
    $dbuname = $_SESSION['dbuname'];
    //TODO check that dbuname exists
    if ($_SESSION['isguest']=='true'){
        $displayname = 'Guest';
    }
    else {
        $displayname = $sessionuname;
    }
}
else{
    $displayname = "not logged in";//TODO could just use an if statement in the HTML
    $sessionuname = "not logged in";
    //Alert and go back to home. There is a message there about needing to be logged in
    //should there be an alert?
    phpAlertandgo("You need to log in or register to do mapping",'login');
}

//got to here so we are doing mapping.
//open the database and find the icons
$mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
if ($mysqli) { //got database
    //go get the image icons
    $allmyicons = array();
    $dummyIcon = new Icon(999,'','','');
    $thestuff = $dummyIcon->dbnames;
    $table = "mapicons";
    $sql="SELECT $thestuff FROM $table";
    $result=mysqli_query($mysqli,$sql);
    //TODO check you got something!
    //$result->num_rows
    //I couldn't think of a smart way to do this
    $nicons = 0;
    $icondir = $config['icondir'];
    while ($obj = mysqli_fetch_object($result)) {
        $fulliconname = htmlspecialchars($_SERVER['DOCUMENT_ROOT']).$icondir.$obj->name.".png";
        if (file_exists($fulliconname)) {
            $allmyicons[$nicons++] = new Icon($obj->ID, $obj->name, $obj->altval, $obj->description);
        }
        else {
            $message .= "<br>Missing file ".$fulliconname;
        }
    }
    // Free result set
    mysqli_free_result($result);
    //close connection
    mysqli_close($con);
}
else{ //couldn't connect to db
    $message = "Database Connect error.<br>" . $config['syserror'];
}

//are we continuing or did it break?
if ($message != "") {//we have to go somewhere else
    $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
    header($errorPageMessage);
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php doheadermin($pagetitle) ?>
    <script type="text/javascript" src="/js/mapping.js"></script>
</head>
<?php
   if ($backfromsave){
       echo "<body onload=\"alert('A draft of your map has been saved.')\">";
   }
   else echo "<body>";
?>
<?php dotopbit2($loggedin,$displayname) ?>
<!--script>
document.addEventListener("dragover", function( event ) {ecx = event.clientX;ecy = event.clientY}, false);
</script-->
<span ondragover="getcoords(event)">
    <div style="padding: 0px 10px ;">
        <?php //place the icons

        foreach ($allmyicons as $anicon){//http://localhost/images/icons/icon3s.png
            $srcname = $anicon->iconname;
            if ($anicon->icondescript!='')$icontitle = $anicon->icondescript;
            else $icontitle = $anicon->iconaltval;//better not be null!
            echo "<img class='icon2' src='$icondir$srcname.png' alt='$anicon->iconaltval' 
title='$icontitle' draggable='true' ondragstart='changeicon(this,event)' ondragend='dropmarker()' id='$anicon->iconID'>
";
        }?>
        <!--img class="icon2" src="/images/icons/home.png"  draggable="true" ondragstart="changeicon(this,event)" ondragend="dropmarker()" id="icon1">
        <img class="icon2" src="/images/icons/bank.png" onclick="changeicon(this)">
        <img class="icon2" src="/images/icons/icon1s.png" onclick="changeicon(this)">
        img class="icon" src="/images/icons/airport.svg" width="20px"-->
    </div>

<div style="position: relative;width: 100%">


    <div class="mappy2" id="map"></div>
    <div class="LHS" id="LHSbig" style="display: none;">
        Some stuff that is hidden initially. May need a max width
        <div class="arrowleft"><img src="arrowin.png" onclick="hideele('LHS')"/></div>
    </div>
    <div class="LHS" id="LHSsmall" display = "block">
    <div class="arrowleft"><img src="arrowout.png" onclick="unhideele('LHS')"/></div>
        </div>
    <!--RHS-->
    <div class="RHS" id="RHSbig" style="display: none;">
        <button onclick="playjson()">Click</button>
      Markers Placed:
        <div class="rT" id="iconlist"></div>
        <div class="arrowright"><img src="arrowout.png"  title="close list" onclick="hideele('RHS')"/></div>
    </div>
    <div class="RHS" id="RHSsmall" display = "block">
        <div class="arrowright"><img src="arrowin.png" title="list your markers" onclick="unhideele('RHS')"/></div>
    </div>

    <!--div class="mapcontentcontainer"-->
    <!--div style="display: table-row">
        <div-- class="mapdialogue">
            <h3>Click on the Map<h3>
                    <div id="theform">
                        <div class="lat-long" id="latbox">
                            Latitude
                        </div>
                        <br>
                        <div class="lat-long" id="lonbox">
                            Longitude
                        </div>
                        <br><br>
                        <form id="sampleForm" name="sampleForm" method="post" action="phpscript.php">
                            <input type="hidden" name="lat" id="lat" value="10">
                            <input type="hidden" name="lon" id="lon" value="10">
                            <button class="lat-long" id="asubmit" onclick="setValue();">Submit</button>
                        </form>
                    </div>

        </div-->
    <!--div class="spacy">&nbsp;</div-->
        <div class="mapcontainer" style="position: relative;display:table-cell;">

        </div>
       <form id="markerForm" name="markerForm" method="post" action="savemap.php">
					<input type="hidden" name="markersjson" id="markersjson" value="">
           <input type="hidden" name="savetype" id="savetype" value="temp">
					<button class="lat-long" id="submitmarkers" onclick="playjson();">Submit</button>
       </form>
    <!--/div-->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBcNYflMeXlK4itfmIDTSxv5cp_J8k4pvE&callback=myMap"></script>

    <!--?php echo "debug values: <br>session status is ".session_status()."<br>";
    echo "session name is ".session_name()."<br>";
    echo "session username is ".$_SESSION['sessionuname']."<br>";
    echo "session ID is ".session_id();
    echo "The current timeout is ".ini_get('session.gc_maxlifetime');?-->
    <?php dofooter() ?>
</span>
</body>
</html>
