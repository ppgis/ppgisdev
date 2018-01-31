<?php
//TODO free mysqli stuff and close connections
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "mappingfns.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$pagetitle = "Exit Survey";

$testforguest = $config['testforguest'];
$loggedin = false;


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
    $displayname = $sessionuname;//not sure why I have both!
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
    $message = "You need to log in or register to participate";
    phpAlertandgo($message,'login');
}

//got to here so we are doing the survey.
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
        if ($userstage < PPGIS_stage_startsurvey) {
            $userstage = PPGIS_stage_startsurvey;
            change_row($mysqli, 'users', array('stageID'), array($userstage), 'i', 'ID', $uID);
        }
        $_SESSION['userstage'] = $userstage;
        //should we check whether the user has finished the survey?

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
        if ($nicons == 0) $oldusericons = null;
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

<?php doheader2($pagetitle) ?>
<script type="text/javascript" src="/js/mapping.js"></script>
<body>
<?php
echo '<script type="text/javascript">';
echo "var oldusericons = $oldusericons;";
echo "</script>";
?>
<?php dotopbit2($loggedin,$displayname) ?>

    <div class="surveycontainer">
        <div class="mappysmall" id="map"></div>
        <div class="surveydialogue">
            <h2>Exit Survey</h2>
                <form class="surveyform">
                    <label>
                        <span>Your home address?</span>
                    <input id="input1" type="text" placeholder="Home address">
                    </label>
                    <label>
                        <span>Q2</span>
                        <input id="input2" type="text">
                    </label>
                    <label>
                        <span>Q3</span>
                        <input id="input3" type="text">
                    </label>
                </form>

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
