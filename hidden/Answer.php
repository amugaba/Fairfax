<?php
class Answer
{
    public $label;
    public $code;
    public $lowCutoff;
    public $highCutoff;
    public $totalCutoff;

    public $counts;
    public $percents;
    public $totals;

    public function __construct()
    {
        $this->counts = array();
        $this->percents = array();
        $this->totals = array();
    }

    public function getCount($groupCode){
        return $this->counts[intval($groupCode)-1];
    }
    public function getPercent($groupCode){
        return $this->percents[intval($groupCode)-1];
    }

    public function addCount($groupCode, $num){
        $this->counts[intval($groupCode)-1] = floatval($num);
    }
    public function addPercent($groupCode, $num){
        $this->percents[intval($groupCode)-1] = floatval($num);
    }
    public function addTotal($groupCode, $num){
        $this->totals[intval($groupCode)-1] = floatval($num);
    }

    public function getCountArray(){
        $arr = ['answer' => $this->label];
        foreach ($this->counts as $key => $count)
            $arr["v$key"] = $count;
        return $arr;
    }
    public function getPercentArray(){
        $arr = ['answer' => $this->label];
        foreach ($this->percents as $key => $percent)
            $arr["v$key"] = $percent*100;
        return $arr;
    }
}