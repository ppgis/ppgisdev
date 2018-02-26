<?php
define("PPGIS_OTHER",'Other');
function trim_walk(&$value,$key)
{
    $value=trim($value);
}
function getsurveyquestions($mysqli,$table){
    $sql = "SELECT * FROM $table";
    $result = mysqli_query($mysqli, $sql);
    $questions = array();
    //the template table has the questions and possible reponses for dropdowns etc
    //questionID, questiontext, questiontype('text','select','checkbox','textarea'),csv_values
    $qnumber = 0;
    while ($obj=$result->fetch_object()) {
        $valuearray = explode(',', $obj->csv_values);
        $questions[$obj->questionID] = array("qtext" => $obj->questiontext, 'answertype' => $obj->questiontype, 'values' => $valuearray);
        /*$questions[1] = array("qtext"=>"What is your age?",'answertype'=>'select','values'=>array('<20','20-30','30-40','>40'));
        $questions[2] = array("qtext"=>"What pets do you have?",'answertype'=>'checkbox','values'=>array('Dog','Cat','Guinea Pig',PPGIS_OTHER));
        $questions[3] = array("qtext"=>"Any comments?",'answertype'=>'textarea');*/
    }
    return $questions;
}

function addsurveycolumns($mysqli,$thetemplatetable,$theresulttable){
    $sizes = array('text'=>'varchar(50)','select'=>'varchar(25)','checkbox'=>'varchar(25)','radio'=>'varchar(25)','textarea'=>'varchar(400)');
    $questions = getsurveyquestions($mysqli,$thetemplatetable);
    //make a string that is the apdate command to add columns
    $columns = array ();
    $sql = "ALTER TABLE $theresulttable ADD ( ";
    foreach ($questions as $num => $question) {
        $selecttype = $question['answertype'];
        $theQ = "Q$num";
        $addstring = "$theQ ".$sizes[$selecttype];
        //is it a select questiontype?
        array_push($columns, $addstring);
        }
    $addcolstr = trim(implode(',', $columns), ',');
    $sql .= $addcolstr.')';
    $result = mysqli_query($mysqli,$sql);
    return $result;
}

function getsurveyquestionsfromfile($thefilehandle){
    $questions = array();
    $row = 0;
    while (($data = fgetcsv($thefilehandle, 1000, "\t")) !== FALSE) {
        $num = count($data);
        if ($num != 4) {
            echo "<p> Error in line $row: <br /></p>\n";
        } else {
            if ($data[3] == 'NULL') $data[3]= '';// = array("");
            //else $valuearray = explode(',', $data[3]);
            $questions[$data[0]] = array("qtext" => $data[1], 'answertype' => $data[2], 'values' => $data[3]);//valuearray);
        }
        $row++;
    }
    return $questions;
}

function makenewtemplatetable($mysqli){

}

function testsurveyversion($surveyversion){
    $surveythings = array();
    $surveythings['message'] = '';
    if (preg_match('/^[opd][0-9]+$/',$surveyversion)){
        $surveythings['goodtogo'] = true;
        $surveythings['templatetable'] = 'exitsurveytemplate'.$surveyversion;
        $surveythings['surveytable'] = 'exitsurvey'.$surveyversion;
    }
    else{
       $surveythings['goodtogo'] = false;
       $surveythings['message'] = 'bad survey version';
    }
    return $surveythings;
}

function createsurveytable($mysqli,$tablename){
    $fkname = 'fk_'.$tablename;
    //drop it if it already exists
    $result = mysqli_query($mysqli,"SHOW TABLES LIKE '$tablename''");
    if ($result->num_rows == 1) $result = mysqli_query($mysqli,"DROP TABLE $tablename");
    if (!$result) die('Error dropping table');
    $sql = "CREATE TABLE $tablename (userID int(11) NOT NULL,timestamp int(11) NOT NULL, PRIMARY KEY (userID),CONSTRAINT $fkname FOREIGN KEY (userID) REFERENCES users (ID) ON DELETE CASCADE ON UPDATE CASCADE)";
    $result = mysqli_query($mysqli, $sql);
    return $result;
}

function dosurvey($questions,$oldsurveyresult,$action,$surveyversion,$istest){
    //write table creation for survey answers possibly using JSON string of columns
    echo "<form class='smallform' action='$action' method='post'>";
    //save the surveyversion
    echo "<input type='hidden' name='surveyversion' value='$surveyversion'>";
    foreach ($questions as $num=>$question){
        $thetext = $question['qtext'];
        //echo "<p>";

        //echo "\n<label><span>$thetext</span>";
        //echo "</label>";
        $name = "Q$num";
        $thetype = $question['answertype'];

        if ($thetype == 'radio')
            echo "\n<div class='longformtext'>$thetext</div>";
        else {
            echo "\n<div class='formtext'>$thetext</div>";
            echo "<div class='surveyformanswer'>\n";
        }
        switch($thetype){
            case 'checkbox':
                array_walk($question['values'],"trim_walk");
                $othervalue = '';
                if (isset($oldsurveyresult[$name])){
                    $tempvalues = explode('|',$oldsurveyresult[$name]);
                    foreach ($tempvalues as $tempvalue){//search for Other value that wasn't in default list
                        if (!in_array($tempvalue,$question['values']))$othervalue = $tempvalue;
                    }
                }
                $name .= "[]";
                foreach ($question['values'] as $thevalue) {
                    $thevalue = trim($thevalue);
                    if (isset($tempvalues)){
                    $checked = in_array($thevalue,$tempvalues)?'checked':'';}
                    else $checked = '';
                    if ($thevalue != PPGIS_OTHER) {
                        echo "<input class='cbox' name='$name' type='$thetype' value='$thevalue' $checked>$thevalue<br>";
                    }
                    else {
                        $name = "Q$num"."[dummy]";
                        $theid = "Q$num"."dummy";
                        $thenewid = "Q$num"."other";
                        $newname = "Q$num"."[".PPGIS_OTHER."]";
                        if ($othervalue == '') {
                            echo "<input id='$theid' class='cbox' name='$name' type='$thetype' value='$thevalue' onclick='checkitout(this);' $checked>";
                            if ($checked == '')
                                echo "<input id='$thenewid' class='formtext' name='$newname' type='text' default='$thevalue' placeholder='$thevalue (please specify)'>";
                            else echo "<input id='$thenewid' class='formtext' name='$newname' type='text' value='$thevalue'>";
                        }
                        else {
                            echo "<input id='$theid' class='cbox' name='$name' type='$thetype' value='$thevalue' onclick='checkitout(this);' checked>";
                            echo "<input id='$thenewid' class='formtext' name='$newname' type='text' value='$othervalue'>";}
                    }
                }

                break;
            case 'text':
                if (isset($oldsurveyresult[$name])){
                    $tempvalue = $oldsurveyresult[$name];
                    echo "<input name='$name' type='$thetype' value='$tempvalue'>";}
                else echo "<input name='$name' type='$thetype'>";
                break;
            case 'textarea':
                if (isset($oldsurveyresult[$name])){
                    $tempvalue = $oldsurveyresult[$name];
                    echo "<textarea maxlength='400' style='resize:none' name='$name' rows=5 cols=30>".$tempvalue."</textarea> ";}
                else echo "<textarea maxlength='400' style='resize:none' name='$name' rows=5 cols=30></textarea>";
                break;
            case 'select':
                if (isset($oldsurveyresult[$name]))$tempvalue = $oldsurveyresult[$name];
                else $tempvalue = 'xxxx';
                echo "<select name='$name'>";
                foreach ($question['values'] as $thevalue) {
                    if ($thevalue == $tempvalue)
                        echo "<option value='$thevalue' selected>$thevalue</option>";
                    else
                        echo "<option value='$thevalue'>$thevalue</option>";
                }
                echo "</select>";
                break;
            case 'radio':
                array_walk($question['values'],"trim_walk");
                $nvalues = sizeof($question['values']);
                $classn = 'n'.$nvalues;
                $othervalue = '';
                if (isset($oldsurveyresult[$name])){
                    $tempvalues = explode('|',$oldsurveyresult[$name]);
                }
                echo "<br>\n<ul class='likert $classn'>\n";
                foreach ($question['values'] as $thevalue) {
                    echo "\n <li>\n";
                    $thevalue = trim($thevalue);
                    if (isset($tempvalues)){
                        $checked = in_array($thevalue,$tempvalues)?'checked':'';}
                    else $checked = '';

                    echo "<input type='radio' name='$name' value='$thevalue' $checked>";
                    echo "<span>$thevalue</span>";
                    echo "\n </li>\n";

                }
                echo "\n </ul>\n";
                break;
            default:
                echo "<input name='$name' type='$thetype'>";
                break;
        }
        if ($thetype != 'radio') echo "\n</div>\n";
        //echo "</p>";
    }
    echo "<p id='exitsubmit'><input type='submit' value='Submit' class='uq-emerald'></p></form>";

}
function showsurveyfiles($surveyfiles)
{
    if ($surveyfiles == []) {
        echo 'No survey input files found. Check that they exist and try again.';
    } else {
        echo '<form class = "smallform" name="stuff" action="testsurvey.php" method="post">';
        echo '<select name="thefile">';

        foreach ($surveyfiles as $thefile) {
            echo "<option class='uq-emerald' value=\"$thefile\">$thefile</option>";
        }

        echo '</select>';
        echo '<input type="submit" value="Submit selected survey file for testing.">';
        echo '</form>';

    }
}
/*function ee($thing){
    echo ($thing);
    echo ("<br>");
}*/
/**
 * Created by PhpStorm.
 * User: beth
 * Date: 31/01/18
 * Time: 1:08 PM
 */