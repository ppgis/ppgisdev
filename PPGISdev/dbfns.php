<?php
/**
 * Created by PhpStorm.
 * User: beth
 * Date: 15/12/17
 * Time: 7:55 AM
 */
function check_exist($mysqli,$table,$col,$value,$valuetype){
    //TODO have more error catches
    $thequery = "select * from $table where $col = ?";
    $SQL = $mysqli->prepare($thequery);
    $SQL->bind_param($valuetype, $value);
    $SQL->execute();
    $result = $SQL->get_result();
    return $result;
}
function insert_row($mysqli,$table,$columns,$values,$valuetypes){

    $nvals = count($values);
    $n = strlen($valuetypes);

    if (($n==$nvals) && ($nvals == count($columns))) {
        $colstr = "(".$columns[0];
        $qstr = "(?";

        /* with call_user_func_array, array params must be passed by reference */
        $bind_params = array($valuetypes,& $values[0]);

        for ($i=1;$i < $nvals;$i++){
            $colstr .= ",".$columns[$i];
            $qstr .=",?";
            $bind_params[] = & $values[$i];
        }

        $colstr.=")";
        $qstr .=")";

        //ee("INSERT INTO $table $colstr VALUES $qstr");
        //var_dump($bind_params);
        if (!($SQL = $mysqli->prepare("INSERT INTO $table $colstr VALUES $qstr"))){
            $result = "error in mysqli prepare"."INSERT INTO $table $colstr VALUES $qstr";
            return $result;
        };

        //do the binding
        call_user_func_array(array($SQL, 'bind_param'), $bind_params);

        /* Execute statement */
        if (!$SQL->execute()){
            return "error in executing mysql statement";
        };

        /* Fetch result to array */
        $result = $SQL->get_result();
        return $result;
    }
    else  {
        $result = "error in number of columns, values, or value types";
        return $result;
    }
}

function change_row($mysqli,$table,$columns,$values,$valuetypes,$keycol,$keyval){

    $nvals = count($values);
    $n = strlen($valuetypes);

    if (($n==$nvals) && ($nvals == count($columns))) {
        $updatestr = $columns[0]."=?";

        /* with call_user_func_array, array params must be passed by reference */
        $bind_params = array($valuetypes,& $values[0]);

        for ($i=1;$i < $nvals;$i++){
            $updatestr .= ",".$columns[$i]."=?";
            $bind_params[] = & $values[$i];
        }

        if (!($SQL = $mysqli->prepare("UPDATE $table set $updatestr WHERE $keycol= $keyval"))){
            $result = "error in mysqli prepare"."UPDATE $table set $updatestr WHERE $keycol= $keyval";
            return $result;
        };

        //do the binding
        call_user_func_array(array($SQL, 'bind_param'), $bind_params);

        /* Execute statement */
        if (!$SQL->execute()){
            return "error in executing mysql statement";
        };
        /* Fetch result to array */
        $retval = $SQL->get_result();
        return $retval;
    }
    else  {
        $result = "error in number of columns, values, or value types";
        return $result;
    }
}

function getusericons($mysqli,$uID,$icontourl){
    $table = "usericons";
    $thestuff = "*";
    $sql = "SELECT $thestuff FROM $table WHERE userID='$uID'";
    $result = mysqli_query($mysqli, $sql);
    if ($result->num_rows == 0) {
        return NULL;
    }
    else {
        $allrows = [];
        while ($obj = mysqli_fetch_object($result)) {
            $ID = $obj->iconID;
            $iconurl = $icontourl[$ID];
            $tmparray = ['url' => $iconurl, 'lat' => $obj->latitude, 'lng' => $obj->longitude, 'iconID' => $ID];
            array_push($allrows, $tmparray);
        }
        return json_encode($allrows);
    }
}
