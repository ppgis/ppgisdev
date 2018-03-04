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
$backfromsurvey = false;
if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['message']))){
    if (test_input($_GET['message'])=='backfromsave') $backfromsave = true;
    if (test_input($_GET['message'])=='backfromsurvey') $backfromsurvey = true;
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
    $displayname = $sessionuname;
    /*beth old if ($_SESSION['isguest']=='true'){
        $displayname = 'Guest';
    }
    else {
        $displayname = $sessionuname;
    }*/
}
else{
    $displayname = "not logged in";
    $sessionuname = "not logged in";
    //Alert and go back to home. There is a message there about needing to be logged in
    //should there be an alert?
    $message = "You need to log in or register to do mapping";
    phpAlertandgo($message,'login');
}

//got to here so we are doing mapping.
$nicons = 0;
$oldusericons = 'null';
$road = 'null';
//open the database and find the icons
$mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
if ($mysqli) { //got database
    $uname_found = check_exist($mysqli, 'users', 'uname', $dbuname, 's');
    if ($uname_found->num_rows == 1) {
        $obj = mysqli_fetch_object($uname_found);
        $uID = $obj->ID;
        $userstage = $obj->stageID;
        //if user found, update stageID
        if ($userstage < PPGIS_stage_startmapping){
            $userstage = PPGIS_stage_startmapping;
            change_row($mysqli, 'users', array('stageID'), array($userstage), 'i', 'ID', $uID);
        }
        elseif ($userstage > PPGIS_stage_startmapping) {
            $hassaved = true;
        }
        //make sure the session knows which stage  it is at
        $_SESSION['userstage'] = $userstage;

        //get the icons
        $allmyicons = array();
        $icontourl = [];//is this the same?
        $thestuff = '*';
        $table = "mapicons";
        $sql = "SELECT $thestuff FROM $table";
        $result = mysqli_query($mysqli, $sql);
        //TODO check you got something!
        //$result->num_rows
        //I couldn't think of a smart way to do this
        $icondir = $config['icondir'];
        while ($obj = mysqli_fetch_object($result)) {
            $partialiconname = $icondir . $obj->name . ".png";
            $fulliconname = htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . $partialiconname;
            if (file_exists($fulliconname)) {
                $allmyicons[$nicons++] = new Icon($obj->ID, $obj->name, $obj->altval, $obj->description);
                $icontourl[$obj->ID] = $partialiconname;
            } else {
                $message .= "<br>Missing file " . $fulliconname;
            }
        }
        //the user may have icons saved in the table
        $oldusericons = getusericons($mysqli, $uID, $icontourl);
        $nusericons = sizeof($oldusericons);
        if ($nusericons == 0) $oldusericons = 'null';
        //users may have a saved road
        $road = getuserroad($mysqli,$uID);
        if (sizeof($road) == 0) $road = 'null';
        //var_dump($road);die;
        //

    } else {
        $message .= "Couldn't find user in database. " . $config['syserror'];
    }
    // now load up anything that was saved
    // Free result set
    mysqli_free_result($result);
    //close connection
    mysqli_close($mysqli);
} else { //couldn't connect to db
    $message = "Database Connect error.<br>" . $config['syserror'];
}

//are we continuing or did it break?
if ($message != "") {//we have to go somewhere else
    $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
    header($errorPageMessage);
}
//some alerts may be required
$alert = false;

if (($backfromsave)){
   $alert = 'backfromsave';//echo "alert('A draft of your map has been saved.');";
}
elseif (($hassaved) & ($nusericons > 0)){
   $alert = 'loadingsaved';
}


?>
<!DOCTYPE html>
<html>
<head>
    <?php doheadermin($pagetitle) ?>
    <link rel="stylesheet" type="text/css" href="/css/popup.css">
    <script type="text/javascript" src="/js/mapping.js"></script>
    <?php if ($alert) {
        $divToHide = 'alertdiv';
    } else {
        $divToHide = 'loadingdiv';
    }
        echo '<script type="text/javascript">';
        echo "function hideSomething(){document.getElementById('$divToHide').style.display = 'none';}";
        echo "window.onload = setTimeout(function(){hideSomething();},1000);";
        echo '</script>';
    ?>
    <style>
        div.idk {
            width:92%;
            margin: auto;
            height: 800px;
            padding-top:0px;
            background-color: #fafafa;

        }
        div.ex1 {
            background-color: white;
            max-width: 90%;
            margin-left: 50px;
            margin-right: 150px;
            height: 750px;
            overflow: scroll;
            border: solid 10px #f5f6f7;
        }
    </style>
</head>
<body>
<?php
$kmldir = $config['kmldir'];
$prothost = $config['protocol'].'://'.$config['hostname'];
echo '<script type="text/javascript">';
echo "staticmap = false;";
setkmlvars($kmldir,$prothost);
echo "var oldusericons = $oldusericons;";
echo "var userroadpath = $road;";
echo "</script>";
?>
<?php dotopbit2($loggedin,$displayname) ?>

<span ondragover="getcoords(event)">
    <div style="padding: 5px 10px ;">
        <div>Drag menu: Choose an icon and drag it to a specific location on the map.</div>
        <?php //place the icons

        foreach ($allmyicons as $anicon){//http://localhost/images/icons/icon3s.png
            $srcname = $anicon->iconname;
            if ($anicon->icondescript!='')$icontitle = $anicon->icondescript;
            else $icontitle = $anicon->iconaltval;//better not be null!
            echo "<img class='icon2' src='$icondir$srcname.png' alt='$anicon->iconaltval' 
title='$icontitle' draggable='true' ondragstart='changeicon(this,event)' ondragend='dropmarker()' id='$anicon->iconID'>
";
        }?>
    </div>
<?php if ($alert) popupandgo($alert); else showloading();?>

<div style="position: relative;width: 100%">

    <input id="pac-input" class="controls" type="text"
       placeholder="Enter a location." style="display:none">
    <div id="drawRoadPopup" class="alertcard" style="display:none;"><div class="alertcontainer" id="drawRoadPopuptext"></div>
        <div class="alertimage" style="padding: 8px">
            <button onclick="restartRoad()" class="btn">Restart</button><button onclick="drawRoad()" class="btn">I'm done!</button></div></div>
    <div class="idk">
    <div class="ex1 shadowy">
    <div class="mappyold" id="map"></div>
        </div>
        </div>
    <!--LHS popout section follows-->
    <div class="LHS shadowy" id="LHSbig" style="display: block;">
        <div>Click<br> menu</div>
        <hr>
        <div>Map<br>tools</div>
        <img id='targeticon' src="/images/icons/target.svg" width="32 px" title="Search for an address or place" onclick="findlocation()" class="box"><br>
        <img id='roadicon' src="/images/icons/road.svg" width="32 px" title="Add a Road" class="box" onclick = "drawRoad()"><br>
                 <img src="/images/icons/delete.svg" title="Remove markers and road" height="32 px" width="32 px" onclick="removeall()" class="box"><br>
        <hr>
        <div>Help</div>
       <img src="/images/icons/help.svg" width="32 px" title="Help" onclick="gethelp()" class="box"><br>
        <hr>
        <div>Save<br>tools</div>
         <img  src="/images/icons/save.svg" width="32 px" title="Save Map" id="tmpsave" onclick="submitjson('temp');" class="box"><br>
         <img src="/images/icons/fin.svg" width="32 px" title="Finished: Save and Submit" id="finalsave" onclick="submitjson('final');" class="box"><br>

        <div class="arrowleft"><img src="arrowin.png" onclick="hideele('LHS')" style="display: block;"/></div>
    </div>
    <div class="LHS shadowy" id="LHSsmall" style="display: none;">
    <div class="arrowleft shadowy"><img src="arrowout.png" onclick="unhideele('LHS')" style="display: block;"/></div>
    </div>
    <!--RHS popout section follows-->
    <div class="RHS shadowy" id="RHSbig" style="display: block;">
      <div class="centredtext">Markers Placed:</div>
        <div class="centredtext" id="iconlist" ></div>
        <hr>
        <div class="centredtext">User Road:</div>
        <div class="rTC centredtext" id="roadlist"></div>
        <div class="arrowright"><img src="arrowout.png"  title="close list" onclick="hideele('RHS')"/></div>
    </div>
    <div class="RHS shadowy" id="RHSsmall" style="display: none;">
        <div class="arrowright shadowy"><img src="arrowin.png" title="list your markers" onclick="unhideele('RHS')"/></div>
    </div>

    <!--div class="mapcontainer" style="position: relative;display:table-cell;">
    </div-->
       <form id="markerForm" name="markerForm" method="post" action="savemap.php">
					<input type="hidden" name="markersjson" id="markersjson" value="">
           <input type="hidden" name="roadjson" id="roadjson" value="">
           <input type="hidden" name="savetype" id="savetype" value="">
       </form>
    <!--/div-->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBcNYflMeXlK4itfmIDTSxv5cp_J8k4pvE&callback=myMap&libraries=places"></script>

    <!--?php echo "debug values: <br>session status is ".session_status()."<br>";
    echo "session name is ".session_name()."<br>";
    echo "session username is ".$_SESSION['sessionuname']."<br>";
    echo "session ID is ".session_id();
    echo "The current timeout is ".ini_get('session.gc_maxlifetime');?-->
    <?php dofooter() ?>
</span>
<script>

</script>
</body>
</html>
