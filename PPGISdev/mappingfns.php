<?php
class Icon {
    public $iconID,$iconname,$iconaltval,$icondescript;
    public $dbnames = 'ID,name,altval,description';//names of corresponding fields in the mapicon database table
    //make sure that this is in the same order as the constructor args otherwise things will break
    //TODO somewhat fix with variable arguments
    public function __construct($ID,$name,$altval,$descript)
    {
        if (func_num_args()==4) {
            $this->iconID = $ID;
            $this->iconname = $name;
            $this->iconaltval = $altval;
            $this->icondescript = $descript;
        }
    }
}

function tableofmarkers(){
    $stuff = <<<END
    <div class="rT" id="iconlist">
<span class="rTC"><img src="/images/icons/icon3s.png" alt="" width="20" height="20" /></span>
<small class="rTC">123.4567</small>,
<small class="rTC">-57.7859</small>
<br>
<span class="rTC"><img src="/images/icons/icon3s.png" alt="" width="20" height="20" /></span>
<span class="rTC">123.4567</span>,
<span class="rTC">-57.7869</span>
</div>
<button onclick="anotherline()">Click</button>
END;
    echo $stuff;
}