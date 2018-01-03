<?php
/**
 * Created by PhpStorm.
 * User: beth
 * Date: 18/12/17
 * Time: 10:18 AM
 */
function cpmessage($uname,$href){
    $message = <<<EOF
<html>
<body>
<p>Dear $uname,</p>
<p>To reset your UQ PPGIS password click the link below:</p>
<a href="$href">Reset UQ PPGIS password</a>
<p>The link has a limited lifetime.<br>If you did not request a password reset please ignore this message.</p>
<p>Yours truly,</p>
<b>UQ PPGIS admin</b>
</body>
</html>
EOF;
    return $message;
}

function homemessage($loggedin,$displayname){
     if (!$loggedin){
         $stuff = <<<END
    <p>To do some mapping, please <a href="signup.php"> register</a> or <a href="login.php">log in</a>.</p>
END;
     }
     else {
         $stuff = <<<END
<p> Welcome $displayname!</p>Please use the top bar to navigate to the page of your choice.
END;
     }
     echo $stuff;
    return true;
}