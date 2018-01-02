<?PHP
//changes to this may also have to be added to login.php for the guest users.
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";

//check if logged in already and automatically log out if so.


$uname = "";
$pword = "";
$email = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //require '../../configure.php';

    $uname = test_input($_POST['username']);
    $pword = test_input($_POST['password']);
    $pword2 = test_input($_POST['retype_password']);
    $email = test_input($_POST['email']);

    $noemail = false;
    if ($email != "") $emailOK = test_email($email);
    else {
        $emailOK = "";
        $noemail = true;
    }
    $unameOK = test_uname($uname);
    $passwordOK = test_pword($pword)? "" :" Password not OK";
    if ($pword2!== $pword)$diffpword = " Passwords differ!";
    else $diffpword = "";

    $posterror = $emailOK.$unameOK.$passwordOK.$diffpword;

    if ($posterror==="") {

        $database = "login";
        $table = "users";

        $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);

        if ($mysqli) {

            $uname_found = check_exist($mysqli,$table,'uname',$uname,'s');
            $email_found = check_exist($mysqli,$table,'email',$email,'s');

            if ($uname_found->num_rows > 0) {
                $errorMessage = "Username '$uname' is already taken. Please try another.";
            } elseif (($email_found->num_rows > 0)&& !$noemail){
                $errorMessage = "Email address '$email' is already taken. Please try another.";
            }
            else {
                $phash = password_hash($pword, PASSWORD_DEFAULT);
                $colnames = array('uname','password','email');
                $values = array($uname,$phash,$email);
                $valuetypes = 'sss';
                $retval = insert_row($mysqli,$table,$colnames, $values, $valuetypes);

                if (preg_match("/^error/",$retval)){
                    $errorMessage = $retval;
                }
                else //success
                    header("Location: ./login.php");
                //else $errorMessage = $retval;
            }
        } else {
            $errorMessage = "Database Connect error. Please contact the website administrator.";
        }
    }
    else {
        $errorMessage = $posterror." Please contact the website administrator";
        }
}
$h3 = "Sign Up";
?>

<!DOCTYPE html>
<html>
<?php doheader($h3) ?>
<body>
<?php dotopbit($h3) ?>


<div class="contentcontainer">
    <div class="dialogue">
        <div class="error" id="signuperror"><?php echo $errorMessage ?></div>
    <ul>
        <li>use only letters, numbers, dash, or underscore</li>
        <li>password needs to be at least 6 characters in length</li>
        <li>password should not be the same as your username!</li>
        <li>email address will be used for password recovery</li>
    </ul></div>
        <form method="post" action="signup.php" onsubmit="return validate(this)">
            <div class="formtext">Create a username:</div>
            <input type="text" class="lat-long" name="username" required="required"
                   placeholder="Username" pattern="[a-zA-Z0-9_-]{1,25}" title="use a-z A-z 0-9 - _ and no spaces">
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

