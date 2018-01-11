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

function doheadermin($pageTitle){//doesn't have the head tags
    $stuff= <<<END
    <meta charset="UTF-8">
    <title>PPGIS $pageTitle</title>
    <script type="text/javascript" src="/js/validatestuff.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/login.css">
END;
    echo $stuff;
}



function doheader2($pageTitle,$sessionuname,$gotonext,$backhere){
    //header for users who are already logged in
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
    $activepage = test_input($_SERVER['SCRIPT_NAME']);
    //get rid of trailing stuff
    $activepage = preg_replace('/.php.*/','.php',$activepage);
    //get rid of leading stuff
    $activepage = preg_replace('/^.*\//','',$activepage);
$pages = array(
    'Mapping' => 'map.php');//this is for the pages after log in
    //first do the PPGIS link on the LHS of the topbar
    echo "<span class='pagebanner2'>";
    echo "<nav class='darkbg'>";
    echo "<ul style='list-style-type: none'>";
    echo "    <li><a  href='home.php'>PPGIS</a></li>";
    //from now on need to add navactive tag if $activepage
    if ($loggedin){
        if ($activepage=='logout.php') echo "<li><a class='navactive' href='logout.php'>Logout</a></li>";
        else echo "<li><a href='logout.php'>Logout</a></li>";
    } else{
        if ($activepage=='login.php') echo "<li><a class='navactive' href=login.php>Login</a></li>";
        else echo "<li><a href='login.php'>Login</a></li>";

        if ($activepage=='signup.php') echo "<li><a class='navactive' href=signup.php>Sign up</a></li>";
        else {echo "<li><a href='signup.php'>Sign up</a></li>";}
    }
    echo "<li><a href=\"mailhandler.php\">Contact</a></li>";
    if ($activepage=='home.php') echo "<li><a class='navactive' href='home.php'>Home</a></li>";
    else echo "<li><a href='home.php'>Home</a></li>";
    if ($loggedin){
        foreach ($pages as $pagetitle => $pageurl) {
            if ($activepage==$pageurl) echo "<li><a class = 'navactive' href='$pageurl'>$pagetitle</a></li>";
            else echo "<li><a href='$pageurl'>$pagetitle</a></li>";
        }
        echo "<li class='emph navnotli'><i>&#9734 $displayname &#9734</i></li>";
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
<footer class="bottombit darkbg" >
    <a href="mailhandler.php"> Contact</a>
</footer>
END;
    echo $stuff;
}

function phpAlertandgo($msg,$page){
    echo"<script type='text/javascript'>alert('$msg');window.location.href = '$page.php'</script>";

}