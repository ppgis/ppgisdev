<?php
/*
some global constants
 */
define("PPGIS_stage_guest", 0);
define("PPGIS_stage_user", 1);
define("PPGIS_stage_startmapping", 2);
define("PPGIS_stage_hasdraft", 3);
define("PPGIS_stage_finmapping", 4);
define("PPGIS_stage_startsurvey", 5);
define("PPGIS_stage_finsurvey", 6);
define("PPGIS_guestDisplayName", "Guest");
define("PPGIS_map_before_survey",'Before doing the survey please visit the mapping page and Save.');
define("PPGIS_map_before_survey_message",'mapbeforesurvey');
define("PPGIS_administrator","a");
define("PPGIS_planner","p");
define("PPGIS_developer","d");
define("PPGIS_other","o");
define("PPGIS_usertypes",array(PPGIS_planner,PPGIS_developer,PPGIS_other));


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
    $pages = array('Mapping' => 'map.php');//this is for the pages after log in
    //first do the PPGIS home link on the LHS of the topbar
    echo "<nav>";
    echo "<ul style='list-style-type: none'>";
    if ($activepage=='home.php') echo "<li><a class='navactive' href='home.php'>";
    else echo "    <li><a  href='home.php'>";
    echo "PPGIS home</a></li>";
    //from now on need to add navactive tag if $activepage
    if ($loggedin){
        $userstage = $_SESSION['userstage'];
        if ($userstage >= PPGIS_stage_finmapping){
           $pages['Survey'] = 'exitsurvey.php';
        }
    }
    if ($loggedin){
        foreach ($pages as $pagetitle => $pageurl) {
            if ($activepage==$pageurl) echo "<li><a class = 'navactive' href='$pageurl'>$pagetitle</a></li>";
            else echo "<li><a href='$pageurl'>$pagetitle</a></li>";
        }
    }


    if ($loggedin){

        echo "<li class='navnotli'> $displayname<span style='padding: 0px 12px;display: inline-block;height: 100%;vertical-align: middle;'><img src='/images/icons/user2.svg' height='16px'></span>  </li>";
        if ($activepage=='logout.php') echo "<li><a class='navactive' href='logout.php'>Logout</a></li>";
        else echo "<li><a href='logout.php'>Logout</a></li>";
        //echo "<li class='emph navnotli'>&#9734 $displayname &#9734</li>";
    } else{
        if ($activepage=='login.php') echo "<li><a class='navactive' href=login.php>Login</a></li>";
        else echo "<li><a href='login.php'>Login</a></li>";

        if ($activepage=='signup.php') echo "<li><a class='navactive' href=signup.php>Sign up</a></li>";
        else {echo "<li><a href='signup.php'>Sign up</a></li>";}
    }
    echo "</ul>";
echo "</nav>";

}

function dotopbit2old($loggedin,$displayname){
    $activepage = test_input($_SERVER['SCRIPT_NAME']);
    //get rid of trailing stuff
    $activepage = preg_replace('/.php.*/','.php',$activepage);
    //get rid of leading stuff
    $activepage = preg_replace('/^.*\//','',$activepage);
    $pages = array(
        'Mapping' => 'map.php');//this is for the pages after log in
    //first do the PPGIS link on the LHS of the topbar
    echo "<nav class='darkbg'>";
    echo "<ul style='list-style-type: none'>";
    echo "    <li><a  href='home.php'>PPGIS</a></li>";
    //from now on need to add navactive tag if $activepage
    if ($loggedin){
        $userstage = $_SESSION['userstage'];
        if ($userstage >= PPGIS_stage_finmapping){
            $pages['Survey'] = 'exitsurvey.php';
        }
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
END;
    echo $stuff;
}

function dofooter(){
    $stuff=<<<END
<footer class="bottombit" >
<ul style='list-style-type: none'>
    <li><a href="mailhandler.php"> Contact</a></li>
</ul>
</footer>
END;
    echo $stuff;
}
function phpMessageandgo($message,$msgtype){
    $errorPageMessage = "Location: errorpage.php?message=" . $message . "&msgtype=" . $msgtype;
    header($errorPageMessage);
}

function phpAlertandgo($msg,$page){
    echo"<script type='text/javascript'>alert('$msg');document.location.href = '$page.php';</script>";
    die;//otherwise it might not alert and go
}
function ee ($string_message) {
    $_SERVER['SERVER_PROTOCOL'] ? print "$string_message<br />" : print "$string_message\n";
}

function popupandgo($alerttype){
    $alerts = array('backfromsave'=>'A draft has been saved','loadingsaved'=>'Loading saved markers.');
    $images = array('backfromsave'=>'backfromsave.svg','loadingsaved'=>'upload.svg');
$stuff=<<<END
   <div class="alertcard" id='alertdiv' onmouseover="hideSomething();">
   <div class="alertimage">
  <img width='64px' src="/images/icons/$images[$alerttype]" alt="Uploading" style="width:100%">
  </div>
  <div class="alertcontainer">
    <h4>$alerts[$alerttype]</h4> 
  </div>
   </div>
END;
    echo $stuff;
    //document.getElementById('alertdiv').display = 'block';
  //document.getElementById('alertdiv').innerHTML = text;
  //setTimeout(function(){hideit()},timelength);
}
function showloading(){//show loading gif
    echo '<div id="loadingdiv"><img src="/images/icons/loading.gif" style="position:absolute; z-index:-2;top:220px;left:50%"></div>';
}

function doguestform($testforguest){
    $other = PPGIS_other;
    $dev = PPGIS_developer;
    $plan = PPGIS_planner;
$stuff=<<<END
    <form method="post" action="login.php" class="smallform">
        <input type="hidden" name="guesty" value="$testforguest">
        <div class='formtext'><i>continue as a guest of type: </i></div>
        <div class='surveyformanswer'>
            <select name='usertype' style="margin-left:0px;" onchange="this.form.submit()">
                <option value="" disabled selected hidden>Please Choose...</option>
                <option value=$other>Other</option>
                <option value=$dev>Developer</option>
                <option value=$plan>Planner</option>
            </select>
        </div>
    </form>
END;
    echo $stuff;
}
function setkmlvars($kmldir,$prothost){
        $allfiles = scandir("$kmldir", 1);
        $kmlversion = -1;
        foreach ($allfiles as $afile) {
            if (preg_match('/BrisbaneLGA[0-9]+\.kml/', ($afile))) {
                $version = (int)filter_var($afile, FILTER_SANITIZE_NUMBER_INT);
                if ($version > $kmlversion) $kmlversion = $version;
            }
        }
        if  ($kmlversion == -1) echo "var haveBrisbaneLGA = false;";
        else{
            echo "var haveBrisbaneLGA = true;";
            echo "var BrisbaneLGA = '$prothost/kml/BrisbaneLGA$kmlversion.kml';";
        }
}

/*function doguestform($testforguest){
    $other = PPGIS_other;
    $dev = PPGIS_developer;
    $plan = PPGIS_planner;
    $types = "'$other','$dev','$plan')";
    $cmdo = "submitit('".$other."',".$types;
    $cmdd = "submitit('".$dev."',".$types;
    $cmdp = "submitit('".$plan."',".$types;
    $stuff=<<<END
    <form method="post" action="login.php" class="smallform" id="guesttypeform">
        <input type="hidden" name="guesty" value="$testforguest">
        <input type="hidden" id="usertype" name="usertype">
        <span class='formtext'><i>continue as a guest of type: </i></span>
    </form>
<button class='guestbtn' onclick="$cmdd">Developer</button>
<button class='guestbtn' onclick="$cmdp">Planner</button>
<button class='guestbtn' onclick="$cmdo">Other</button>
END;
    echo $stuff;
}*/