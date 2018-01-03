<?php
/**
 * Created by PhpStorm.
 * User: beth
 * Date: 18/12/17
 * Time: 8:43 AM
 */
function send_html_mail($to,$from,$subject,$message){

    $headers  = "From: $from\r\n";
    $headers .= "Content-type: text/html\r\n";
    $mail = mail($to, $subject, $message, $headers);
    if ($mail) {
        return true;
    }
    else {
        return false;
    }
}


function dienicely($debugstatus,$errormessage){
    if ($debugstatus) die($errormessage);
    else //TODO email to sysadmin
        die("Please contact the System Administrator");
    return true;
}


function doheader($pageTitle){
    $stuff= <<<END
<head>
    <meta charset="UTF-8">
    <title>PPGIS $pageTitle</title>
    <script type="text/javascript" src="/js/validatestuff.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/login.css">
</head>
END;
    echo $stuff;
}

function doheader2($pageTitle,$sessionuname,$gotonext,$backhere){
    //header for users who are alreday logged in
    //note that the head tags are missing from this version
    $stuff= <<<END
    <meta charset="UTF-8">
    <title>PPGIS $pageTitle</title>
    <script type="text/javascript" src="/js/validatestuff.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/login.css">
    <script type="text/javascript">
    function confirmLogin(){
        var username  = '$sessionuname';
        var previouspage  = '$gotonext';
        var backhere = '$backhere';
        var needtologout =  confirm('You are already logged in as '+username+'.\\n You will be logged out if you continue. Is that OK?');

        if (needtologout){
            //log out and come back
            window.location.href = 'logout.php?gotonext='+backhere
        }
        else {
            //go back to previous
            window.location.href = previouspage
        }
    }
    </script>
END;
    echo $stuff;
}


function dotopbit($h3){
    $stuff=<<<END
    <div class="pagebanner topbit">
    <H1>PPGIS</H1>
    <div class="whereami"><h3>$h3</h3></div>
</div>
END;
    echo $stuff;
}

function dotopbit2($loggedin,$displayname){

    $stuff=<<<END
<span class="pagebanner2">
<nav class="darkbg">
    <ul style="list-style-type: none">
        <li><a  href="home.php">PPGIS</a></li>
END;
    echo $stuff;
    if ($loggedin){
        echo "<li><a href=logout.php>Logout</a></li>";
    } else{
        echo "<li><a href=login.php>Login</a></li>";
    }
    echo "<li><a href=\"mailhandler.php\">Contact</a></li>";
    echo "<li><a href=\"home.php\">Home</a></li>";
    if ($loggedin){
        echo "<li><a href=map.html>Mapping</a></li>";
        echo "<li class='emph'><i>&#9734 $displayname &#9734</i></li>";
    }
    $stuff = <<<END
    </ul>
</nav>
</span>
END;
    echo $stuff;
}



function dofooter(){
    $stuff=<<<END
<footer class="pagebanner bottombit darkbg" >
    <p><a href="mailhandler.php"> Contact</a></p>
</footer>
END;
    echo $stuff;
}

