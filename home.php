<?php
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "messages.php";
require_once "/usr/local/bin/PPGISdev/messages.php";
$h3 = "home";

session_start();
$_SESSION['comebackto'] = 'home.php';
if (!empty($_SESSION['sessionuname'])) $loggedin = true;
if ($loggedin){
    if ($_SESSION['isguest']=='true')$displayname = 'Guest';
    else $displayname = $_SESSION['sessionuname'];
}
else{
    $displayname = 'not logged in';
}

?>
<!DOCTYPE html>
<html>


<?php doheader($h3) ?>
<body>
<?php dotopbit2($loggedin,$displayname) ?>

<div class="contentcontainer">
    <div class="homedialogue">
        <p>Welcome to <span class="uq_purple">UQ PPGIS</span>.</p>
        <?php homemessage($loggedin,$displayname) ?>
        </div>
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
