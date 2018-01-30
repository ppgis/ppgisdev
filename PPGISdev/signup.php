<?PHP
//changes to this may also have to be added to login.php for the guest users.
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";

$pagetitle = "Sign Up";

$goodlogin = "Sign up worked";
$errorMessage = "";//will display on page
$msgtype='bad';
$message = "";//will display on error page


session_start();
//were to go back to after login?
$gotonext = empty($_SESSION['comebackto'])? 'home.php': $_SESSION['comebackto'];
$phpgotonext = "Location: ".$gotonext;

$loggedin = false;

//already have a session?
if (!empty($_SESSION['sessionuname'])){
    //get uname
    $sessionuname = $_SESSION['sessionuname'];
    $msgtype='nice';
    $loggedin = true;
    $backhere = 'signup.php';
    $displayname = $sessionuname;
    /*beth old if ($_SESSION['isguest']=='true'){
        $displayname = 'Guest';
    }
    else {
        $displayname = $sessionuname;
    }*/
}
else {
    $displayname = "not logged in";
    $sessionuname = "not logged in";
    $uname = "";
    $pword = "";
    $email = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //require '../../configure.php';
        $signupvars = array('username'=>'uname','password'=>'pword','retype_password'=>'pword2','email'=>'email');

        foreach ($signupvars as $postname => $varname){
            if (isset($_POST[$postname])) $$varname = test_input($_POST[$postname]);
            else $$varname = "";

        }

        $noemail = false;
        if ($email != "") $emailOK = test_email($email);
        else {
            $emailOK = "";
            $noemail = true;
        }

        $unameOK = test_uname($uname);
        $passwordOK = test_pword($pword) ? "" : " Password not OK";
        if ($pword2 !== $pword) $diffpword = " Passwords differ!";
        else $diffpword = "";

        $posterror = $emailOK . $unameOK . $passwordOK . $diffpword;
        //note that an error at the POST stage indicates nefarious activity

        if ($posterror === "") {

            $database = "login";
            $table = "users";

            $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);

            if ($mysqli) {

                $uname_found = check_exist($mysqli, $table, 'uname', $uname, 's');
                $email_found = check_exist($mysqli, $table, 'email', $email, 's');

                if ($uname_found->num_rows > 0) {
                    $errorMessage = "Username '$uname' is already taken. Please try another.";
                } elseif (($email_found->num_rows > 0) && !$noemail) {
                    $errorMessage = "Email address '$email' is already taken. Please try another.";
                } else {
                    $phash = password_hash($pword, PASSWORD_DEFAULT);
                    $colnames = array('uname', 'password', 'email','stageID');
                    $values = array($uname, $phash, $email,1);//stageID 1 for registered user
                    $valuetypes = 'sssi';
                    $retval = insert_row($mysqli, $table, $colnames, $values, $valuetypes);

                    if (preg_match("/^error/", $retval)) {
                        $message = $retval;
                    } else //success
                        $message = $goodlogin;
                        $sessionuname = $uname;
                    //else $errorMessage = $retval;
                }
            } else {
                $message = "Database Connect error. ". $config['syserror'];
            }
        } else {
            $message = "Registration problem. ".$posterror." ". $config['syserror'];
        }
    }
}

//OK what now?

if ($message != "") {//we have to go somewhere else
//if login worked
    if ($message === $goodlogin) {
        //successful signup
        $_SESSION['stage'] = '1';//registered as a user
        $_SESSION['login'] = "1";
        $_SESSION['sessionuname'] = $sessionuname;
        $_SESSION['dbuname'] = $sessionuname;
        /*beth old $_SESSION['isguest'] = '0'; */
        header($phpgotonext);
    } else {
//if something bad happened
        $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
        header($errorPageMessage);
    }
}


?>

<!DOCTYPE html>
<html>
<head>
<?php doheader2($pagetitle,$sessionuname,$gotonext,$backhere) ?>
</head>
<body>
<?php dotopbit2($loggedin,$displayname) ?>
<?php if ($loggedin) echo "<script type='text/javascript'> window.onload = confirmLogin();</script>";   ?>

<div class="contentcontainer">
    <div class="dialogue">
        <div class="error" id="signuperror"><?php echo $errorMessage ?></div>
    <ul>
        <li>use only letters, numbers, dash, or underscore</li>
        <li>password needs to be at least 6 characters in length</li>
        <li>password should not be the same as your username!</li>
        <li>email address will be used for password recovery</li>
    </ul></div>
        <form method="post" action="signup.php" onsubmit="return validate(this,'upre')">
            <div class="formtext">Create a username:</div>
            <input type="text" class="lat-long" name="username" required="required"
                   placeholder="Username" pattern="[a-zA-Z0-9_-]{3,25}" title="at least 3 characters from a-z A-z 0-9 - _ and no spaces">
            <div class="formtext">and a password:<br>
                    <button type="button" id="shbutton" tabindex="-1" onclick="showhide('shbutton',['password','retype_password'])">(show)</button></div>
            <input type="password" class="lat-long" name="password" pattern="[a-zA-Z0-9_-]{6,}" required="required"
                   placeholder="Password" title="use at least 6 of a-z A-z 0-9 - _ and no spaces">
            <div class="formtext">retype the password:</div>
            <input type="password" class="lat-long" name="retype_password" required="required">
            <p>
            <div class="formtext">(Optional) email address</div>
            <input type="email" class="lat-long" name="email"
                   placeholder = "email@address">
            <p><input type="submit"></p>
        </form>
    <!--TODO put a back to home link-->
    </div>





    </body>
</html>

