<?php
class Icon {
    public $iconID,$iconname,$iconaltval,$icondescript;
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