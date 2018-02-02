<?php
define("PPGIS_OTHER",'Other');
function trim_walk(&$value,$key)
{
    $value=trim($value);
}
function getsurveyquestions($mysqli){
    $table = "exitsurveytemplate";
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
function dosurvey($questions,$oldsurveyresult){
    echo "<form class='smallform' action='saveexitsurvey.php' method='post'>";
    foreach ($questions as $num=>$question){
        $thetext = $question['qtext'];
        //echo "<p>";
        echo "\n<div class='formtext'>$thetext</div>";
        //echo "\n<label><span>$thetext</span>";
        //echo "</label>";
        $name = "Q$num";
        $thetype = $question['answertype'];
        echo "<div class='surveyformanswer'>\n";
        switch($thetype){
            case 'checkbox':
                array_walk($question['values'],"trim_walk");
                $othervalue = '';
                if (isset($oldsurveyresult[$name])){
                    $tempvalues = explode('|',$oldsurveyresult[$name]);
                    foreach ($tempvalues as $tempvalue){
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
            default:
                echo "<input name='$name' type='$thetype'>";
                break;
        }
        if ($thetype == 'checkbox'){

        }
        echo "\n</div>\n";
        //echo "</p>";
    }
    echo "<p id='exitsubmit'><input type='submit' value='Submit' class='uq-emerald'></p></form>";

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