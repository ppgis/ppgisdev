<?php
require_once "test_input.php";
require_once "usefuls.php";
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
$requestType = $_SERVER['REQUEST_METHOD'];
$message = "";
$msgtype = 'default';


switch ($requestType) {
    case 'GET':
        if (isset($_GET['message'])) $message = htmlspecialchars($_GET['message']);//clean it up
        if (isset($_GET['msgtype'])) $msgtype = htmlspecialchars($_GET['msgtype']);
        break;
    default:
        header($config['gotodefault']);//request type that isn't being handled.
        break;
}

if ($message == PPGIS_map_before_survey_message){
    $message = PPGIS_map_before_survey;
    $thepage = 'map.php';
    $thename = 'mapping';
}
else {
    $thename = 'home';
    $thepage = $config['homepage'];
}

$loggedin = false;
session_start();
if (!empty($_SESSION['sessionuname'])) {
    $loggedin = true;
    $displayname = $_SESSION['sessionuname'];
    if (($msgtype != 'bad') & ($msgtype != 'success')) {
        $message = "Hello $displayname!<br>" . $message;
    }
}
else {
    $displayname = 'not logged in';
}

/*beth old if (!empty($_SESSION['sessionuname'])) $loggedin = true;
if ($loggedin){
    if ($_SESSION['isguest']=='true'){
        $displayname = 'Guest';
    }
    else {
        $displayname = $_SESSION['sessionuname'];
    }
}
else{
    $displayname = 'not logged in';
}*/


switch ($msgtype){
    case 'bad':
        $pagetitle = "Unrecoverable error detected";
        $errorclass = 'error';
        break;
    case 'success':
        $pagetitle = "Success!";
        $errorclass = 'notanerror';
        break;
    case 'nice':
        $pagetitle = "Alert";
        $errorclass = 'notanerror';
        break;
    default:
        $pagetitle = "An error has occurred";
        $errorclass = 'error';
        break;
}




?>
<!DOCTYPE html>
<html>
<?php doheader($pagetitle) ?>
<body>
<?php dotopbit2($loggedin,$displayname) ?>

<div class="contentcontainer">

    <?php echo "<div class='$errorclass centredtext' id='signuperror'><h3>$pagetitle</h3>$message</div>"; ?>
        <div class="lat-long centredtext">Please use the top bar to navigate to the page of your choice, or <br><a href="<?php echo($thepage)?>">head to PPGIS <?php echo($thename)?></a>.

</div>

</div>
        <?php dofooter() ?>
</body>
</html>