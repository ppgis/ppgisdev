<?php
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$h3 = "mapping";
$activepage = "map.php";
//$thing = test_input($_SERVER['SCRIPT_NAME']);
//$thing = preg_replace('/^\//','',$thing);
$testforguest = $config['testforguest'];
$loggedin = false;

session_start();
$_SESSION['comebackto'] = 'map.php';
//were to go back to after login if required?
$gotonext =  $_SESSION['comebackto'];
$phpgotonext = "Location: ".$gotonext;

$isloggedin = false;

//already have a session?
if (!empty($_SESSION['sessionuname'])){
    //this will automatically make the user confirm that they want to continue
    $sessionuname = $_SESSION['sessionuname'];
    $msgtype='nice';
    $isloggedin = true;
    $dbuname = $_SESSION['dbuname'];
}
else{
    //Alert and go back to home. There is a message there about needing to be logged in
    //should there be an alert?
    phpAlertandgohome("You need to log in or register to do mapping");
}


?>
<!DOCTYPE html>
<html>

<?php doheader($h3) ?>
<body>
<?php dotopbit2($isloggedin,$sessionuname,$activepage) ?>

<!--?php echo "debug values: <br>session status is ".session_status()."<br>";
echo "session name is ".session_name()."<br>";
echo "session username is ".$_SESSION['sessionuname']."<br>";
echo "session ID is ".session_id();
echo "The current timeout is ".ini_get('session.gc_maxlifetime');?-->
<?php dofooter() ?>

</body>
</html>
