<?PHP
//BETH do some initial checking that the token is unused, not expired, and
//that it corresponds to some valid userID
/*
 **cprequest**
 request a password change
 *Normal Flow:*
 * will come from a get request
 * validate the token and go no further if it fails
 */
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";

$requestType = $_SERVER['REQUEST_METHOD'];

$loggedin = false;

$goodlogin = "Password change worked";

session_start();

//were to go back to after change password?
$gotonext = empty($_SESSION['comebackto'])? 'home.php': $_SESSION['comebackto'];
$phpgotonext = "Location: ".$gotonext;

if (!empty($_SESSION['sessionuname'])) {
    $loggedin = true;
    $displayname = $_SESSION['sessionuname'];
}
else {
    $displayname = 'not logged in';
}
/*beth old if ($loggedin){
    $displayname = $_SESSION['sessionuname'];
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


$msgtype='bad';
$iscleantoken = false;
$message = "";
$errorMessage = "";

$needswarning = false;

//Not getting in without a token
switch ($requestType) {
    case 'POST':
        if (isset($_POST['token'])) {
            $token = test_input($_POST['token']);//clean it up
            $iscleantoken = true;
        }
        else $message = "unrecognized POST value";//not a valid call to this page
        break;
    case 'GET':
        if ($loggedin) $needswarning = true;
        if (isset($_GET['token'])) {
            $token = test_input($_GET['token']);//clean it up
            $iscleantoken = true;
        }
        else $message = "unrecognized GET value";//not a valid call to this page
        break;
    default:
        $message = "unrecognized request method";//request type that isn't being handled.
        break;
}


if ($iscleantoken){ //check for goodness

    $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);
    if ($mysqli) {//connected to DB OK

        $table = "cptoken";
        $token_exists = check_exist($mysqli, $table, 'token', $token, 's');
        if ($token_exists->num_rows < 1) {
            //$debug = $token;
            $message = "token was not found in the database";
        } else {
            $stuff = $token_exists->fetch_assoc();//get mysqli result into an array
            $changetime = $stuff["changed"];
            $expiry = $stuff["timestamp"];
            $UID = (int)$stuff["UID"];
            $thetime = time();

            //note that if both used and expired only used message will show
            $msgtype = 'nice';
            if ($expiry < $thetime) $message = "The link has expired. Please visit the login page to generate another.";
            if (!is_null($changetime)) {
                $message = "The link has already been used to change your password. Please visit the login page to generate another.";

            }

        }
    }
    else{
        $message = "Database Connect error.<br>".$config['syserror'];
    }

}

//if nothing went wrong
if ($message == "") {

    $pword = "";
    $pword2 = "";
    //check that the user exists
    $table = "users";
    $valuetypes = 's';
    $keycol = 'ID';
    $keyval = $UID;
//get the username to be nice
    $user_exists = check_exist($mysqli, $table, $keycol, $keyval, 's');
    if ($user_exists->num_rows < 1) {
        $message = "Corresponding user was not found in database";
    } else {
        $stuff = $user_exists->fetch_assoc();//get mysqli result into an array
        $uname = $stuff["uname"];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//TODO test if these POST variables exist
            $pword = test_input($_POST['password']);
            $pword2 = test_input($_POST['retype_password']);

            if (test_pword($pword) && ($pword2 == $pword)) {

                //done previously $UID = (int)$stuff["UID"];

                $table = "users";
                $phash = password_hash($pword, PASSWORD_DEFAULT);
                $colnames = array('password');
                $values = array($phash);
                $valuetypes = 's';
                $keycol = 'ID';
                $keyval = $UID;


                $retval = change_row($mysqli, $table, $colnames, $values, $valuetypes, $keycol, $keyval);
                //check that it worked
                if (preg_match("/^error/", $retval)) {
                    $message = $retval . $config['syserror'];
                } else {
                    $table = "cptoken";
                    $colnames = array('changed');
                    $values = array($thetime);
                    $valuetypes = 'i';
                    $keycol = 'token';
                    $keyval = "'$token'";
                    $retval = change_row($mysqli, $table, $colnames, $values, $valuetypes, $keycol, $keyval);
                    //check that it worked
                    if (preg_match("/^error/", $retval)) {
                        $message = $retval . $config['syserror'];
                    } else {
                        $msgtype = 'success';
                        $message = $goodlogin;
                    }
                }
            } else {
                $message = "Bad POST";
                header("Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype);
            }

        }

    }
}

if ($message !="") {
    if ($message === $goodlogin) {
        //log in
        // session_start() has been done;
        $_SESSION['login'] = "1";
        $_SESSION['sessionuname'] = $uname;
        $_SESSION['dbuname'] = $uname;
        //not a guest after signup!!!
        /*beth old $_SESSION['isguest'] = '0';*/
        header($phpgotonext);
    } else {
        $errorMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
        header($errorMessage);
    }
}
//else

$pagetitle = "password change";

?>

<!DOCTYPE html>
<html>
<head>
<?php doheadermin($pagetitle) ?>
<script type="text/javascript">
    function loggedinAlert(){
        alert("You are already logged in. If you continue, you will be logged out and a new session started.");
    }
</script>
</head>

<body>
<?php dotopbit2($loggedin,$displayname) ?>
<?php if ($needswarning) echo "<script type='text/javascript'> window.onload = loggedinAlert();</script>";   ?>

<div class="contentcontainer">
    <div class="dialogue">
        <div class="error" id="signuperror"><?php echo $errorMessage ?></div>
        <ul>
            <li>use only letters, numbers, dash, or underscore</li>
            <li>password needs to be at least 6 characters in length</li>
            <li>password should not be the same as your username!</li>
            <li>email address will be used for password recovery</li>
        </ul></div>
    <form method="post" action="cp.php" onsubmit="return validate(this,'pr')">
        <?php if ($message ==="") echo "<div class='formtext'>New password for $uname:<br>"; ?>
        <button type="button" id="shbutton" tabindex="-1" onclick="showhide('shbutton',['password','retype_password'])">(show)</button>
</div>
<input type="password" class="lat-long" name="password" pattern="[a-zA-Z0-9_-]{6,}" required="required"
       placeholder="Password" title="use at least 6 of a-z A-z 0-9 - _ and no spaces">
<div class="formtext">retype the password:</div>
<input type="password" class="lat-long" name="retype_password" required="required">
<p>
    <?php if ($message ==="") echo "<input type='hidden' name='token' value=$token>"; ?>
<p><input type="submit"></p>
</form>

</div>


<?php dofooter() ?>

</body>
</html>

