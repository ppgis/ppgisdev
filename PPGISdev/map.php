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
    if ($_SESSION['isguest']=='true'){
        $displayname = 'Guest';
    }
    else {
        $displayname = $sessionuname;
    }
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
$oldusericons = 'null';
//open the database and find the icons
$mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
if ($mysqli) { //got database
    $uname_found = check_exist($mysqli, 'users', 'uname', $dbuname, 's');
    if ($uname_found->num_rows == 1) {
        $obj = mysqli_fetch_object($uname_found);
        $uID = $obj->ID;
        $userstage = $obj->stageID;
        //if user found update stageID
        if ($userstage < 3) {
            $userstage = 3;
            change_row($mysqli, 'users', array('stageID'), array($userstage), 'i', 'ID', $uID);
        }
        elseif ($userstage > 3) {
            $hassaved = true;
        }
        $_SESSION['stageID'] = $userstage;

        if ($obj->stageID < 3) {
            change_row($mysqli, 'users', array('stageID'), array(3), 'i', 'ID', $uID);
        }//
        //get the icons
        $allmyicons = array();
        $icontourl = [];//is this the same?
        $dummyIcon = new Icon(999, '', '', '');
        $thestuff = $dummyIcon->dbnames;
        $table = "mapicons";
        $sql = "SELECT $thestuff FROM $table";
        $result = mysqli_query($mysqli, $sql);
        //TODO check you got something!
        //$result->num_rows
        //I couldn't think of a smart way to do this
        $nicons = 0;
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
        //the user may have icons saved in either the temp or the permanent table
        $oldusericons = getusericons($mysqli, $uID, $icontourl);
        $nicons = sizeof($oldusericons);
        //

    } else {
        $message .= "Couldn't find user in database. " . $config['syserror'];
    }
    // now load up anything that was saved
    // Free result set
    mysqli_free_result($result);
    //close connection
    mysqli_close($con);
} else { //couldn't connect to db
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
    <?php doheader2($pagetitle) ?>
    <script type="text/javascript" src="/js/mapping.js"></script>
</head>
<body>
<?php
echo '<script type="text/javascript">';
if (($backfromsave) & ($nicons > 0)){
    echo "alert('A draft of your map has been saved.');";
}
elseif (($hassaved) & ($nicons > 0)){
    echo "alert('Loading saved markers.');";
}
echo "var oldusericons = $oldusericons;";
echo "</script>";
?>
<?php dotopbit2($loggedin,$displayname) ?>

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
    </div>


<div style="position: relative;width: 100%">


    <div class="mappy2" id="map"></div>
    <!--LHS popout section follows-->
    <div class="LHS" id="LHSbig" style="display: block;">
       <img src="/images/icons/help.svg" width="32 px" title="Help" onclick="gethelp()" class="box"><br>
         <img  src="/images/icons/draft.svg" width="32 px" title="Save Draft" onclick="submitjson('temp');" class="box"><br>
         <img src="/images/icons/check-form.svg" height="32 px" width="32 px" title="Finished: Save and Submit" onclick="submitjson('final');" class="box"><br>
         <img src="/images/icons/delete.svg" title="Remove all markers" height="32 px" width="32 px" onclick="removeall()" class="box"><br>
        <div class="arrowleft"><img src="arrowin.png" onclick="hideele('LHS')" style="display: block;"/></div>
    </div>
    <div class="LHS" id="LHSsmall" style="display: none;">
    <div class="arrowleft"><img src="arrowout.png" onclick="unhideele('LHS')" style="display: block;"/></div>
        </div>
    <!--RHS popout section follows-->
    <div class="RHS" id="RHSbig" style="display: none;">
      Markers Placed:
        <div class="rT" id="iconlist"></div>
        <div class="arrowright"><img src="arrowout.png"  title="close list" onclick="hideele('RHS')"/></div>
    </div>
    <div class="RHS" id="RHSsmall" display = "block">
        <div class="arrowright"><img src="arrowin.png" title="list your markers" onclick="unhideele('RHS')"/></div>
    </div>

        <div class="mapcontainer" style="position: relative;display:table-cell;">

        </div>
       <form id="markerForm" name="markerForm" method="post" action="savemap.php">
					<input type="hidden" name="markersjson" id="markersjson" value="">
           <input type="hidden" name="savetype" id="savetype" value="">
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
