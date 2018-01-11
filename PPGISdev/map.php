<?php
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$pagetitle = "mapping";

//$thing = test_input($_SERVER['SCRIPT_NAME']);
//$thing = preg_replace('/^\//','',$thing);
$testforguest = $config['testforguest'];
$loggedin = false;

$activepage = test_input($_SERVER['SCRIPT_NAME']);
//get rid of trailing stuff
$activepage = preg_replace('/.php.*/','.php',$activepage);
//get rid of leading stuff
$activepage = preg_replace('/^.*\//','',$activepage);

session_start();//TODO put this in a separate file for inclusion
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


?>
<!DOCTYPE html>
<html>
<head>
<?php doheadermin($pagetitle) ?>
    <script type="text/javascript" src="/js/mapping.js"></script>
</head>
<body>
<?php dotopbit2($loggedin,$displayname) ?>
<div class="mapcontentcontainer">
    <div style="display: table-row">
        <img class="icon2" src="/images/icons/home.png" onclick="changeicon(this,event)" draggable="true" ondragstart="changeicon(this,event)" ondragend="dropmarker(event,this)" id="icon1">
        <img class="icon2" src="/images/icons/bank.png" onclick="changeicon(this)">
        <img class="icon2" src="/images/icons/bike.png" onclick="changeicon(this)">
        <!--img class="icon" src="/images/icons/airport.svg" width="20px"-->
    </div>
    <div style="display: table-row">
        <div class="mapdialogue">
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

        </div>
        <!--div class="spacy">&nbsp;</div-->
        <div class="mapcontainer" style="position: relative;display:table-cell;">
            <div class="mappy" id="map"></div>
            <img class="icon" src="/images/icons/bank.png" id="cornericon"/>
        </div>
    </div>

</div>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBcNYflMeXlK4itfmIDTSxv5cp_J8k4pvE&callback=myMap"></script>
<!--?php echo "debug values: <br>session status is ".session_status()."<br>";
echo "session name is ".session_name()."<br>";
echo "session username is ".$_SESSION['sessionuname']."<br>";
echo "session ID is ".session_id();
echo "The current timeout is ".ini_get('session.gc_maxlifetime');?-->
<?php dofooter() ?>

</body>
</html>
