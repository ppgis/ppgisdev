<?php
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "mappingfns.php";
require_once "surveyfns.php";
require_once "/usr/local/bin/PPGISdev/messages.php";


//get the session stuf
$loggedin = false;

$msgtype='bad';
$message = "";//will display on error page. anything in here will send us to the error page

//check survey version is OK
if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['surveyversion']))){
    $surveyversion = test_input($_POST['surveyversion']);
    $surveythings = testsurveyversion($surveyversion);
}
if ($surveythings['goodtogo']) {
    session_start();
//is something stuffs up, go home and not back to here
    $_SESSION['comebackto'] = 'home.php';
//where to go back to if some stuffup happens?
    $gotonext = $_SESSION['comebackto'];
    $phpgotonext = "Location: " . $gotonext;

//already have a session?
    if (!empty($_SESSION['sessionuname'])) {

        $dbuname = $_SESSION['dbuname'];

        //open the database
        $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
        if ($mysqli) { //got database
            //get uid
            $uname_found = check_exist($mysqli, 'users', 'uname', $dbuname, 's');
            if ($uname_found->num_rows == 1) {
                $obj = mysqli_fetch_object($uname_found);
                $usertype = $obj->usertype;
                //the user must be an administrator
                if ($usertype >= PPGIS_administrator) {
                    $uID = $obj->ID;//now see if there are any entries already in the exitsurvey table
                    //clone the temporary table
                    $thetable = $surveythings['templatetable'];
                    $sql = "SHOW TABLES like '$thetable'";
                    $result = mysqli_query($mysqli,$sql);
                    if ($result->num_rows==1){
                        $surveyexists = true;
                        $sql = "DELETE FROM $thetable WHERE 1";
                    }
                    else {
                        $surveyexists = false;
                        $sql = "CREATE TABLE $thetable LIKE tempsurveytemplate";
                    }
                    $result1 = mysqli_query($mysqli, $sql);
                    $sql = "INSERT $thetable SELECT * from tempsurveytemplate";
                    $result2 = mysqli_query($mysqli, $sql);
                    if ($result1 && $result2){
                        //TODO update the version number in a table I have yet to create
                        $theresulttable = $surveythings['surveytable'];
                        $result = createsurveytable($mysqli,$theresulttable);
                        if (!$result) die ('Error creating survey table.');
                        $result = addsurveycolumns($mysqli,$thetable,$theresulttable);
                        if (!$result) die ('Error inserting columns into new table.');
                        //finally at this point, it should have worked!
                    }
                    else $message = 'SQL create table failed';
                }else {//not an admin!
                    $msgtype = 'nice';
                    $message = 'Only administrators can use that function.';
                }
            } else {
                $message = "User ID not found in database" . $config['syserror'];
            }
        } else { //couldn't connect to db
            $message = "Database Connect error.<br>" . $config['syserror'];
        }
    } else {
        $message = "Your login has expired or did not exist! " . $config['syserror'];
    }
}
else $message = "No survey data found.";

if ($message == ''){
    if ($surveyexists){
        $message = "You have successfully updated survey, version $surveyversion";
    }
    else {
       $message = "You have successfully created a new survey, version $surveyversion.";
    }
    $msgtype = 'success';
}
   $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
   header($errorPageMessage);

?>
