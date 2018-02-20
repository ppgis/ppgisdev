<?php
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$pagetitle = "savemap";

$msgtype='bad';
$message = "";//will display on error page. anything in here will send us to the error page


if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['markersjson'])) && (isset($_POST['savetype']))){
    //var_dump($_POST['markersjson']);
    $savetype = test_input($_POST['savetype']);
    //what is the min stage at this point?
    $minstage = ($savetype=="final") ? PPGIS_stage_finmapping: PPGIS_stage_hasdraft;
    $table = "usericons";
    $table2 = "userroads";
    $markersjson = test_json_input($_POST['markersjson']);
    $roadjson = test_json_input($_POST['roadjson']);
    $markers = json_decode($markersjson,true);
    $road = json_decode($roadjson,false);
    //var_dump($road==NULL);die;
    //get dbuname from session
    session_start();
    //already have a session?
    if (!empty($_SESSION['dbuname'])){
        $dbuname = $_SESSION['dbuname'];
        //open the database
        $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
        if ($mysqli) {
            //get the uid
            $uname_found = check_exist($mysqli, 'users', 'uname', $dbuname, 's');
            if ($uname_found->num_rows  == 1) {
                //get user ID
                $obj = mysqli_fetch_object($uname_found);
                $uID = $obj->ID;
                $userstage = $obj->stageID;
                //remove everything from savedmarkers table for that uid
                $sql = "DELETE from $table WHERE userID = $uID";
                $deleted=mysqli_query($mysqli,$sql);
                $nrows = $mysqli->affected_rows;
                //now remove any saved roads
                $sql = "DELETE from $table2 WHERE userID = $uID";
                $deleted=mysqli_query($mysqli,$sql);
                $nrows = $mysqli->affected_rows;
                //echo ("So, $nrows rows deleted");
                //update the user icons table
                $updatetable = $table;
                $colnames = array('userID', 'iconID','latitude','longitude');
                foreach ($markers as $iconID=>$marker){
                      //$iconID = $marker['type'];
                      //echo($iconID);var_dump($marker);
                      $lats = $marker['lats'];
                      //var_dump($marker['lats']);ee("lats");
                      $longs = $marker['longs'];
                      for ($i = 0;$i < sizeof($lats);$i++){
                          $values = array($uID, (int)$iconID,(double)$lats[$i],(double)$longs[$i]);
                          $valuetypes = 'iidd';
                          $retval = insert_row($mysqli, $updatetable, $colnames, $values, $valuetypes);
                          if (preg_match("/^error/", $retval)) {
                              $message = $retval;
                          }
                      }
                }
                //update the user roads table
                $updatetable = $table2;
                $colnames = array('userID', 'latitude','longitude');
                if ($road != NULL){
                    //var_dump($road);
                     foreach ($road as $roadpoint){
                         //var_dump($roadpoint);
                         $values = array($uID, (double)$roadpoint[0],(double)$roadpoint[1]);
                         $valuetypes = 'idd';
                         $retval = insert_row($mysqli, $updatetable, $colnames, $values, $valuetypes);
                         if (preg_match("/^error/", $retval)) {
                             $message = $retval;
                         }
                     }
                }

                //update the user stage
                if ($userstage < $minstage){
                    $userstage = $minstage;
                    change_row($mysqli, 'users', array('stageID'), array($userstage), 'i', 'ID', $uID);
                }
                //make sure the session knows which stage  it is at
                $_SESSION['userstage'] = $userstage;

            }else{
                $message = "User not found in database.";
            }
        }
        else {
            $message = "Database connection error";
        }
    }
    else{
        //Alert and go back to home. There is a message there about needing to be logged in
        //should there be an alert?
        phpAlertandgo("Oh no! Your session expired and any unsaved data has been lost.",'login');
    }


}
else {
    $message = "No data was received.";
}

//are we continuing or did it break?
if ($message != "") {//we have to go somewhere else
    $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
    header($errorPageMessage);
}
else {
    if ($savetype == "final") header("Location:exitsurvey.php");
        else header("Location:map.php?message=backfromsave");
}

//            newmarkertype = {'type':currentID,'n': 1,'src':currentmarker,'lats':[location.lat()],
//'longs':[location.lng()],'nmarker':[nmarkers]};


?>

