<?PHP
//CHANGE this and you may also need to change the guest login script
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "/usr/local/bin/PPGISdev/messages.php";

$pagetitle = "Login";

$goodlogin = "Login worked";
$errorMessage = "";//will display on page
$msgtype='bad';
$message = "";//will display on error page
$backhere = 'login.php';//in case we need to logout and come back here
/*beth old $isguest = '0';//whether this user is a guest or not*/
$loggedin = false;
$testforguest = $config['testforguest'];

$uname = "";
$pword = "";
//changed session time in php.ini
// server should keep session data for AT LEAST 2 hours
//ini_set('session.gc_maxlifetime', 7200);
// each client should remember their session id for EXACTLY 2 hours
//session_set_cookie_params(7200);
session_start();

//were to go back to after login?
$gotonext = empty($_SESSION['comebackto'])? 'home.php': $_SESSION['comebackto'];
$phpgotonext = "Location: ".$gotonext;




//already have a session?
if (!empty($_SESSION['sessionuname'])){
    //this will automatically make the user confirm that they want to continue
    //get uname
    $sessionuname = $_SESSION['sessionuname'];
    $msgtype='nice';
    $loggedin = true;
    $displayname = $_SESSION['sessionuname'];
/*beth old    if ($_SESSION['isguest']=='true'){
        $displayname = 'Guest';
    }
    else {
        $displayname = $_SESSION['sessionuname'];
    }
*/
}
else {//there is no session yet
    $displayname = "not logged in";
    $sessionuname = "not logged in";
    $isaguest = false;
    $badguest = true;

    //did they click continue as guest? (Could come from post or get)
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['guesty']))){
        $isaguest = true;
        if (test_input($_POST['guesty'])===$testforguest){
            $badguest = false;
        };
    }
    if (($_SERVER['REQUEST_METHOD'] == 'GET') && (isset($_GET['guesty']))){
        $isaguest = true;
        if (test_input($_GET['guesty'])===$testforguest){
            $badguest = false;
        };
    }


    if ($isaguest || ($_SERVER['REQUEST_METHOD'] == 'POST')) {
        //guest first
        if ($isaguest) {
            //check for a valid guest
            if (!$badguest) {
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
                        $colnames = array('uname', 'password', 'email','stageID');
                        $values = array($uname, $phash, $email,0);//status 0 for guest user
                        $valuetypes = 'sssi';
                        $retval = insert_row($mysqli, $table, $colnames, $values, $valuetypes);

                        if (preg_match("/^error/", $retval)) {
                            $errorMessage = $retval;
                        } else {
                            /*beth old $isguest = '1';*/
                            $sessionuname = PPGIS_guestDisplayName;//'Guest';
                            $dbuname = $uname;
                            $message = $goodlogin;
                            $userstage = '0';
                        }

                    } else {//must have found this guest but we will let them continue
                        /*beth old $isguest = '1'; */
                        $sessionuname = PPGIS_guestDisplayName;//'Guest';
                        $dbuname = $uname;
                        $message = $goodlogin;
                        $userstage = '0';
                    }
                 //todo save a login event to the database

                } else {
                    $message = "Database Connect error.<br>" . $config['syserror'];
                }
            } else {//bad guest!
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
                        /*beth old $isguest = '0';*/
                        $sessionuname = $uname;
                        $dbuname = $uname;
                        $userstage = $db_field['stageID'];

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
//if login worked START a SESSION!!!
    if ($message === $goodlogin) {
        // session_start() has been done;
        $_SESSION['login'] = "1";
        $_SESSION['sessionuname'] = $sessionuname;
        $_SESSION['dbuname'] = $dbuname;
        /*beth old $_SESSION['isguest'] = $isguest;*/
        $_SESSION['userstage'] = $userstage;
        header($phpgotonext);
    } else {
//if something bad happened TODO make sure session is unset?
        $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
        header($errorPageMessage);
    }
}
//always show the change password link
$displaycp = 'block';
//here's another option if something not so bad happened
//if ($errorMessage != "") $displaycp = 'block';
//else $displaycp = 'none';


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
    </div>
    <div style="text-align:left;">
    <form method="post" action="login.php" onsubmit="return validate(this,'up')" class="smallform">
        <div class="formtext">Username:</div>
        <input type="text" name="username" required="required"
               placeholder="Username" pattern="[a-zA-Z0-9_-]{1,25}" title="use a-z A-z 0-9 - _ and no spaces"><br>
        <div class="formtext">Password:<button type="button" id="shbutton" tabindex="-1" onclick="showhide('shbutton',['password'])">(show)</button></div>
        <input type="password"  name="password" pattern="[a-zA-Z0-9_-]{6,}" required="required"
               placeholder="Password" title="use at least 6 of a-z A-z 0-9 - _ and no spaces">
    </div>
        <p class="centredtext" ><input type="submit" value='Submit' class="uq-emerald" style="text-align:center"></p>
    </form>

    <div class="goto centredtext" ><a href="cprequest.php" style="display:<?php echo $displaycp?>">Forgotten Password?</a></div>
    <div style="background-color: white;width: 100%">
        <form method="post" action="login.php">
            <input type="hidden" name="guesty" value="<?php echo $testforguest?>">
            <p class="centredtext"><input class="uq-emerald" type="submit" value="Continue as Guest" id="guesty"></p>
        </form>
    </div>
</div>


<?php dofooter() ?>
</body>
</html>

