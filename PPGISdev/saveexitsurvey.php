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

if (($_SERVER['REQUEST_METHOD'] == 'POST') && (count($_POST) >= 1) && isset($_POST['surveyversion'])) {

    session_start();
//is something stuffs up, go home and not back to here
    $_SESSION['comebackto'] = 'home.php';
//where to go back to if some stuffup happens?
    $gotonext = $_SESSION['comebackto'];
    $phpgotonext = "Location: " . $gotonext;

//already have a session?
    if (!empty($_SESSION['sessionuname'])) {

        $dbuname = $_SESSION['dbuname'];
        //get the surveyversion
        $surveyversion = test_input($_POST['surveyversion']);

        //open the database
        $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
        if ($mysqli) { //got database
            //get uid
            $uname_found = check_exist($mysqli, 'users', 'uname', $dbuname, 's');
            if ($uname_found->num_rows == 1) {
                $updatetable = 'exitsurvey';
                $obj = mysqli_fetch_object($uname_found);
                $uID = $obj->ID;//now see if there are any entries already in the exitsurvey table
                $oldsurveyversion = $obj->surveyversion;
                if ($oldsurveyversion != NULL) {//there may be saved survey results
                    $surveythings = testsurveyversion($oldsurveyversion);
                    if (!$surveythings['goodtogo']) phpMessageandgo($surveythings['message'],$msgtype);
                    $oldtable = $surveythings['surveytable'];
                    $oldsurveyresults = check_exist($mysqli, $oldtable, 'userID', $uID, 'i');
                    if ($oldsurveyresults->num_rows != 0) {
                        //delete the old row
                        $sql = "DELETE from $oldtable WHERE userid = '$uID'";
                        $result = mysqli_query($mysqli, $sql);
                    }
                }
                    $surveythings = testsurveyversion($surveyversion);
                    if (!$surveythings['goodtogo']) phpMessageandgo($surveythings['message'],$msgtype);
                    $templatetable = $surveythings['templatetable'];
                $updatetable = $surveythings['surveytable'];
                $columns = array('userID');
                $values = array($uID);
                $valuetypes = 'i';
                //need those survey questions
                $questions = getsurveyquestions($mysqli,$templatetable);
                foreach ($questions as $num => $question) {
                    $theQ = "Q$num";
                    ee($theQ);
                    //is it a select questiontype?
                    $selecttype = ($question['answertype'] == 'select');
                    array_push($columns, $theQ);
                    $valuetypes .= 's';
                    if (isset($_POST[$theQ])) {
                        //var_dump($_POST[$theQ]);echo gettype($_POST[$theQ]);echo "<br>";
                        if ($selecttype & in_array($_POST[$theQ], $question['values'])) {
                            //set the value to one of the allowed values
                            $thevalue = $_POST[$theQ];
                        } elseif (is_array($_POST[$theQ])) {

                            //sanitise the array
                            $postvalue = $_POST[$theQ];
                            array_walk($postvalue, "walk_test_input");
                            if ($postvalue[PPGIS_OTHER] != '') {
                                unset($postvalue['dummy']);
                            }
                            $thevalue = trim(implode('|', $postvalue), '|');
                        } else $thevalue = test_input($_POST[$theQ]);

                    } else $thevalue = '';
                    array_push($values, $thevalue);
                }
                array_push($columns, 'timestamp');
                array_push($values, time());
                $valuetypes .= 'i';
                //echo "here";var_dump($values);echo"<br>";var_dump($columns);
                //save to database
                //var_dump($values);var_dump($columns);die;
                $retval = insert_row($mysqli, $updatetable, $columns, $values, $valuetypes);
                if (preg_match("/^error/", $retval)) {
                    $message .= $retval;
                }
                else {
                    $sql = "UPDATE users set surveyversion = '$surveyversion' where ID = '$uID' ";
                    $result = mysqli_query($mysqli,$sql);
                    if (!$result) $message="Error updating survey version for user " . $config['syserror'];
                }

            } else {
                $message = "User ID not found in database" . $config['syserror'];
            }
        } else { //couldn't connect to db
            $message = "Database Connect error.<br>" . $config['syserror'];
        }
    } else {
        $message = "No login found on survey exit. " . $config['syserror'];
    }
}
else $message = "No POST data found.";

if ($message == ''){
    $message = 'Thank you for completing the PPGIS Mapping and Survey.';
    $msgtype = 'success';
}
   $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
   header($errorPageMessage);

?>
