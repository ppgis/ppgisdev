<?php
//TODO free mysqli stuff and close connections
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "mappingfns.php";
require_once "surveyfns.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$pagetitle = "Load and Test Exit Survey";

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

}
else{
    $displayname = "not logged in";
    $sessionuname = "not logged in";
    //Alert and go back to home. There is a message there about needing to be logged in
    //should there be an alert?
    $message = "You need to log in as an administrator for this function";
    phpAlertandgo($message,'login');
}


//see if we have an admin user

//open the database
    $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
    if ($mysqli) { //got database
        $uname_found = check_exist($mysqli, 'users', 'uname', $dbuname, 's');
        if ($uname_found->num_rows == 1) {
            $obj = mysqli_fetch_object($uname_found);
            $uID = $obj->ID;
            $usertype = $obj->usertype;
            //the user must be an administrator
            if ($usertype == PPGIS_administrator) {

                if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                    $allfiles = scandir("/tmp", 1);
                    $surveyfiles = array();
                    foreach ($allfiles as $afile) {
                        if (preg_match('/survey[opd][0-9]+\.txt/', strtolower($afile))) {
                            array_push($surveyfiles, $afile);
                        }
                    }
                }
                else {//we should know the survey filename
                    $thefile = test_input($_POST['thefile']);
                    //echo $thefile . "<br>";
                    $surveyversion = str_replace('survey','',strtolower($thefile));
                    $surveyversion = str_replace('.txt','',$surveyversion);
                    $row = 1;
                    if (($handle = fopen("/tmp/$thefile", "r")) !== FALSE) {
                        $questions = getsurveyquestionsfromfile($handle);
                        fclose($handle);
                        if (count($questions)==0) $message = 'No readable questions found';
                    } else {
                        $message = "Error: survey input file not found";
                    }
                    if ($message == '') {
                        //empty the temp table
                        $temptable = 'tempsurveytemplate';
                        $sql = "TRUNCATE TABLE $temptable";
                        $result = mysqli_query($mysqli, $sql);
                        //write the new questions to the temp table
                        $sql = "describe $temptable";
                        $result = mysqli_query($mysqli,$sql);
                        $colnames = array();
                        while($record = mysqli_fetch_array($result)){
                            array_push($colnames,$record['0']);
                        }
                       //hopefully have colnames now
                        //var_dump($questions);
                        foreach ($questions as $questionID=>$questionsarray) {
                            //var_dump($questionsarray['qtext']);
                            $values = array($questionID,$questionsarray['qtext'] ,$questionsarray['answertype'],$questionsarray['values']);
                            $valuetypes = 'isss';
                            $retval = insert_row($mysqli, $temptable, $colnames, $values, $valuetypes);
                        }
                        $questions = getsurveyquestions($mysqli,$temptable);
                        if ($questions) {
                            $dosurveyform = true;
                            $questionids = array_keys($questions);
                            $sizes = getradiosizes($questions);
                        } else {
                            $dosurveyform = false;
                            $message = "Couldn't read survey questions from temporary table. " . $config['syserror'];
                        }
                    }
                }

            } else {
                $msgtype = 'nice';
                $message = 'Only administrators can use that function.';
            }
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


?>
<!DOCTYPE html>
<html>
<head>
    <?php doheadermin($pagetitle) ?>
    <link rel="stylesheet" type="text/css" href="/css/survey.css">
    <?php if ($dosurveyform && (count($sizes)>0)) {
                doradios($sizes);
            }
            ?>
</head>
<body>
<script type="text/javascript" src="/js/mapping.js"></script>
<?php
echo '<script type="text/javascript">';
echo "var oldusericons = $oldusericons;";
echo "var userroadpath = $road;";
echo "</script>";
?>
<?php dotopbit2($loggedin,$displayname) ?>

    <div class="surveycontainer">
        <div class="surveydialogue">

            <?php if ($dosurveyform) {
                echo "<h2 style='text-align: center;'>Simulation of new Exit Survey from $thefile</h2>";
                dosurvey($questions,$oldsurveyresult,'savenewsurvey.php',$surveyversion,true);
            }
            else {
                echo '<h2 style="text-align: center;">Choose Exit Survey template file</h2>';
                showsurveyfiles($surveyfiles);
            }?>
        </div>

    </div>

<!--?php echo "debug values: <br>session status is ".session_status()."<br>";
echo "session name is ".session_name()."<br>";
echo "session username is ".$_SESSION['sessionuname']."<br>";
echo "session ID is ".session_id();
echo "The current timeout is ".ini_get('session.gc_maxlifetime');?-->
<?php dofooter() ?>

</body>
</html>
