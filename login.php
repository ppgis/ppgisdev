<?PHP
//CHANGE this and you may also need to change the guest login script
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "/usr/local/bin/PPGISdev/messages.php";

$goodlogin = "Login worked";
$errorMessage = "";//will display on page
$msgtype='bad';
$message = "";//will display on error page

$uname = "";
$pword = "";


session_start();
//were to go back to after login?
$gotonext = empty($_SESSION['comebackto'])? 'home.php': $_SESSION['comebackto'];
$phpgotonext = "Location: ".$gotonext;

$isloggedin = false;

//already have a session?
if (!empty($_SESSION['sessionuname'])){
    //get uname
    $sessionuname = $_SESSION['sessionuname'];
    $msgtype='nice';
    $isloggedin = true;
    $backhere = 'login.php';
}
else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //require '../../configure.php';
        if (isset($_POST['guesty']) && true) {
            //clean up the input
            $isguesty = test_input($_POST['guesty']);
            //check for a valid guest
            if ($isguesty == "isguesty") {
                //do guest login
                // connect to db
                $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);

                if ($mysqli) {

                    $table = 'users';
                    $token = bin2hex(random_bytes(5));
                    $uname = $token . "guest";
                    $uname_found = check_exist($mysqli, $table, 'uname', $uname, 's');
                    if ($uname_found->num_rows == 0) {//new guest otherwise re-use
                        //random password
                        $pword = bin2hex(random_bytes(6));
                        $phash = password_hash($pword, PASSWORD_DEFAULT);
                        //unique email includes ip address
                        $ip = preg_replace('/\s+/', '', $_SERVER['REMOTE_ADDR']);
                        $email = preg_replace('/@/', '', $ip . $uname);
                        $colnames = array('uname', 'password', 'email');
                        $values = array($uname, $phash, $email);
                        $valuetypes = 'sss';
                        $retval = insert_row($mysqli, $table, $colnames, $values, $valuetypes);

                        if (preg_match("/^error/", $retval)) {
                            $errorMessage = $retval;
                        } else {
                            $isguest = '1';
                            $sessionuname = 'Guest';
                            $message = $goodlogin;
                        }
                    } else {//must have found this guest but we will let them continue
                        $isguest = '1';
                        $sessionuname = 'Guest';
                        $message = $goodlogin;
                    }


                } else {
                    $message = "Database Connect error.<br>" . $config['syserror'];
                }
            } else {
                $message = "Bad POST";
            }

        } elseif (isset($_POST['username']) && isset($_POST['password'])) {
            //do normal login
            //clean up the input
            $uname = test_input($_POST['username']);
            $pword = test_input($_POST['password']);

            $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);

            if ($mysqli) {

                $table = "users";
                $user_found = check_exist($mysqli, $table, 'uname', $uname, 's');

                if ($user_found->num_rows == 1) {

                    $db_field = $user_found->fetch_assoc();//get the row values into an associative array

                    if (password_verify($pword, $db_field['password'])) {
                        $message = $goodlogin;
                        $isguest = '0';
                        $sessionuname = $uname;
                    } else {
                        $errorMessage = "Username/Password mismatch";
                        //session_start();
                        //$_SESSION['login'] = '';
                    }
                } else {
                    $errorMessage = "Username/Password mismatch";
                }
            } else {
                $message = "Database Connect error.<br>" . $config['syserror'];
            }
        } else {
            $message = "Empty POST";
            //no POST values were set. reload the form
        }
    }
}

//OK what now?

if ($message != "") {//we have to go somewhere else
//if login worked
    if ($message === $goodlogin) {
        //this was already done session_start();
        $_SESSION['login'] = "1";
        $_SESSION['sessionuname'] = $sessionuname;
        $_SESSION['isguest'] = $isguest;
        header($phpgotonext);
    } else {
//if something bad happened
        $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
        header($errorPageMessage);
    }
}
//if something not so bad happened
if ($errorMessage != "") $displaycp = 'block';
else $displaycp = 'none';
$h3 = "Login";
?>

<!DOCTYPE html>
<html>
<head>
    <?php doheader2($h3,$sessionuname,$gotonext,$backhere) ?>
</head>
<body>
<?php dotopbit($h3) ?>

<?php if ($isloggedin) echo "<script type='text/javascript'> window.onload = confirmLogin();</script>";   ?>

<div class="contentcontainer">
    <div class="dialogue">
        <div class="error" id="signuperror"><?php echo $errorMessage ?></div>
    </div>
    <form method="post" action="login.php" onsubmit="return validatelogin(this)">
        <div class="formtext">Username:</div>
        <input type="text" class="lat-long" name="username" required="required"
               placeholder="Username" pattern="[a-zA-Z0-9_-]{1,25}" title="use a-z A-z 0-9 - _ and no spaces">
        <div class="formtext">Password:<br>
            <button type="button" id="shbutton" tabindex="-1" onclick="showhide('shbutton',['password'])">(show)</button></div>
        <input type="password" class="lat-long" name="password" pattern="[a-zA-Z0-9_-]{6,}" required="required"
               placeholder="Password" title="use at least 6 of a-z A-z 0-9 - _ and no spaces">
        <p><input type="submit" ></p>
    </form>
    <div class="goto" ><a href="cprequest.php" style="display:<?php echo $displaycp?>">Forgotten Password?</a></div>
    <div style="background-color: white;width: 100%">
        <form method="post" action="login.php">
            <input type="hidden" name="guesty" value="isguesty">
            <p><input type="submit" value="Continue as Guest" id="guesty"></p>
        </form>
    </div>
</div>


<?php dofooter() ?>
</body>
</html>

