<?php
require_once "test_input.php";
$logoutgotos = array('home.php','login.php','signup.php','cp.php');//the first one is the default
if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['gotonext']))) {
//require '../../configure.php';
    $gotonext = test_input($_GET['gotonext']);
}
else $gotonext = $logoutgotos[0];
//make sure it's OK
if (!in_array($gotonext,$logoutgotos)) $gotonext = $logoutgotos[0];
session_start();
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(),'',0,'/');
session_regenerate_id(true);
$gotonext = "Location: ".$gotonext;
header($gotonext);
?>
