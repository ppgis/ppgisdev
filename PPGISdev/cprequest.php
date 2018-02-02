<?PHP
/*
 **cprequest**
 request a password change
 *Normal Flow:*
 empty page-load to start
 get a valid email address from the user
 this goes to a POST load
 get the associated UID from the users table
 generate a random token
 insert a row into the cptoken table
 send an email to the given address with a url link
 *Other Flow*
 as before, starting with the POST
 */
date_default_timezone_set('Australia/Brisbane');
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
require_once "test_input.php";
require_once "dbfns.php";
require_once "usefuls.php";
require_once "/usr/local/bin/PPGISdev/messages.php";

$loggedin = false;
session_start();
if (!empty($_SESSION['sessionuname'])) {
    $loggedin = true;
    $displayname = $_SESSION['sessionuname'];
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
} */



$email = "";
$errorMessage = "";//will display on page
$displayform="block";
$msgtype='bad';
$message = "";//will display on error page

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['email'])) {
        $email = test_input($_POST['email']);
        $emailOK = test_email($email);
    }
    else $emailOK = "no email address entered. ";

    if ($emailOK == "") {
        $mysqli = new mysqli('localhost', $config['uname'], $config['password'], $config['dbname']);

        if ($mysqli) {

            $table = "users";
            $email_found = check_exist($mysqli, $table, 'email', $email, 's');

            if ($email_found->num_rows < 1) {
                $errorMessage = "Email address '$email' was not found in the database. Please try another.";
            } else {
                // the email address was found.
                // Get the UID and then
                // Put an entry into the cptoken table and send an email

                $stuff = $email_found->fetch_assoc();//get mysqli result into an array
                $UID = (int)$stuff["ID"];
                $uname = $stuff["uname"];
                $expiry = time() + 3600;
                $token = bin2hex(random_bytes(12));

                $colnames = array('token', 'UID', 'timestamp');
                $values = array($token, $UID, $expiry);
                $valuetypes = 'sii';
                $table = "cptoken";
                $retval = insert_row($mysqli, $table, $colnames, $values, $valuetypes);
                // did  the insert work?
                if (preg_match("/^error/", $retval)) {
                    $message = $retval;
                } else {
                    //send email
                    $protocol = $config['protocol'];
		    $hostname = $config['hostname'];
                    $deployment = $config['deployment'];
                    $href = "$protocol://$hostname/$deployment/cp.php?token=$token";
                    $mailmessage = cpmessage($uname, $href);

                    $to = $email;
                    $from = $config['adminemail'];
                    $subject = "reset UQ PPGIS password";

                    $mail_sent = send_html_mail($to, $from, $subject, $mailmessage);

                    //did the sendmail work?
                    if ($mail_sent) {
                        $msgtype = "success";
                        $message = "Please check your email.";
                    } else $message = "Error sending email" . $config['syserror'];
                    //else $errorMessage = $retval;
                }
            }
        } else {
            $message = "Database Connect error.<br>" . $config['syserror'];
        }
    }
    else $message = "Invalid email address. ". $config['syserror'];
}

if ($message !="") {
    $errorPageMessage = "Location: errorpage.php?message=".$message."&msgtype=".$msgtype;
    header($errorPageMessage);
}

$pagetitle = "Request password change";

?>
<!DOCTYPE html>
<html>
<?php doheader($pagetitle) ?>
<body>
<?php dotopbit2($loggedin,$displayname) ?>


<div class="contentcontainer">
    <div class="dialogue">
        <div class="error" id="signuperror"><?php echo $errorMessage ?></div>
    </div>
    <form method="post" action="cprequest.php" onsubmit="return validate(this,'e')" class='smallform'>
        <div class="formtext" style="vertical-align: baseline;">Enter the email address of your PPGIS account:</div>
        <input type="email"  name="email" required="required"
               placeholder = "email@address">
        <p class="centredtext"><input type="submit" value='Submit' class="uq-emerald" style="text-align:center"></p>
    </form>

</div>
<?php dofooter() ?>

</body>
</html>

