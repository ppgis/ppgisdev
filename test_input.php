<?php
/**
 * Created by PhpStorm.
 * User: beth
 * Date: 14/12/17
 * Time: 6:52 PM
 */
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function test_uname($uname){
    //these test result strings will not usually be seen
    //but may be useful when debugging
    $result = "";
    $unamelength = strlen($uname);
    //test length
    if ($unamelength < 3) $result = "Invalid username: make it longer";
    if ($unamelength > 25) $result = "Invalid username: make it shorter";
    //test for the word 'guest'
    if (strpos($uname, 'guest') !== false) $result="Sorry, the word guest is not allowed in a username.";
    //test for bad characters
    if (preg_match("/[^a-zA-Z0-9-_]/", $uname)) $result = "Invalid username";
    return $result;
}
function test_pword($pword){
    $result = true;
    //test length
    if (strlen($pword) < 6) $result = false;
    //test for bad characters
    if (preg_match("/[^a-zA-Z0-9-_]/", $pword)) $result = false;
    return $result;
}
function test_email($email){
    $result = "";
    if (preg_match("/.*guest$/",$email)) $result = "Email taken";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $result = "Invalid Email";
    return $result;
}

?>