<?php
/**
 * Created by PhpStorm.
 * User: beth
 * Date: 18/12/17
 * Time: 11:43 AM
 */
$config = parse_ini_file('/usr/local/bin/PPGISdev/config.ini');
$thestring = $config['adminemail'];
$the = "o:";
$mid = "ailt";
header("Location: m$mid$the$thestring");
exit();
?>