<?php
require_once "usefuls.php";
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
$requestType = $_SERVER['REQUEST_METHOD'];
$message = "";

switch ($requestType) {
    case 'GET':
        $message = htmlspecialchars($_GET['message']);//clean it up
        $msgtype = htmlspecialchars($_GET['msgtype']);
        break;
    default:
        header($config['gotodefault']);//request type that isn't being handled.
        break;
}

switch ($msgtype){
    case 'bad':
        $h3 = "Unrecoverable error detected";
        break;
    case 'success':
        $h3 = "Success!";
        break;
    case 'nice':
        $h3 = "Warning";
        break;
    default:
        $h3 = "An error has occurred";
        break;
}

?>
<!DOCTYPE html>
<html>
<?php doheader($h3) ?>
<body>
<?php dotopbit($h3) ?>

<div class="contentcontainer">

        <div class="error centredtext" id="signuperror"><?php echo $message; ?></div>
        <div class="lat-long"><a href="<?php echo($config['homepage'])?>">Go to our Home Page</a>

</div>

</div>
        <?php dofooter() ?>
</body>
</html>