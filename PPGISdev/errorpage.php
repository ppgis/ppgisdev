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

$loggedin = false;
session_start();
if (!empty($_SESSION['sessionuname']) & ($msgtype != 'bad') & ($msgtype != 'success')) {
    $loggedin = true;
    $displayname = $_SESSION['sessionuname'];
    $message = "Hello $displayname!<br>".$message;
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
        break;
    case 'success':
        $pagetitle = "Success!";
        break;
    case 'nice':
        $pagetitle = "Alert";
        break;
    default:
        $pagetitle = "An error has occurred";
        break;
}

?>
<!DOCTYPE html>
<html>
<?php doheader($pagetitle) ?>
<body>
<?php dotopbit2($loggedin,$displayname) ?>

<div class="contentcontainer">

        <div class="error centredtext" id="signuperror"><?php echo $pagetitle.'<br>'.$message; ?></div>
        <div class="lat-long centredtext">Please use the top bar to navigate to the page of your choice, or <br><a href="<?php echo($config['homepage'])?>">head to PPGIS home</a>.

</div>

</div>
        <?php dofooter() ?>
</body>
</html>