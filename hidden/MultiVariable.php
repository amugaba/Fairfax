<?php
/**
 * A MuliVariable is a single question like "How old are you?" that has multiple answers like "12", "13", "14", etc.
 * The data for that variable would look like:
 * code = "I1"
 * question = "How old are you?"
 * summary = "Age"
 * category = 99 (Demographics)
 * answers = array of Answer objects, [Answer for age 10, Answer for age 11, etc.]
 * totals ???
 */
require_once 'Variable.php';

class MultiVariable extends Variable
{
    public $labels = [];

    public function addCount($answerCode, $groupCode, $num){
        $this->counts[intval($answerCode)-1][intval($groupCode)-1] = floatval($num);
    }
    public function addTotal($groupCode, $num){
        $this->totals[intval($groupCode)-1] = floatval($num);
    }
    public function calculatePercents(){
        for($i=0; $i < count($this->counts); $i++) {
            for($j=0; $j < count($this->counts[$i]); $j++) {
                if($this->totals[$j] == 0)
                    $this->percents[$i][$j] = 0;
                else
                    $this->percents[$i][$j] = round($this->counts[$i][$j] / $this->totals[$j] * 100, 1);
            }
        }
    }
    /* FLIP CROSSTAB GRAPH - Need to fix non-grouped graphs if doing this (total wrong)
    public function calculatePercents(){
        for($i=0; $i < count($this->counts); $i++) {
            for($j=0; $j < count($this->counts[$i]); $j++) {
                if($this->totals[$i] == 0)
                    $this->percents[$i][$j] = 0;
                else
                    $this->percents[$i][$j] = round($this->counts[$i][$j] / $this->totals[$i] * 100, 1);
            }
        }
    }*/
    public function initializeCounts($groupVar){
        $groupLength = $groupVar == null ? 1 : count($groupVar->labels);
        for($i=0; $i < count($this->labels); $i++) {
            for($j=0; $j < $groupLength; $j++) {
                $this->counts[$i][$j] = 0;
                $this->totals[$j] = 0;
            }
        }
    }

    public function getTotal($groupCode){
        return $this->totals[intval($groupCode)-1];
    }

    public function getSumPositives() {
        $positives = [];
        foreach ($this->counts as $answerPositives) {
            $positives[] = array_sum($answerPositives);
        }
        return $positives;
    }

    public function getSumTotal(){
        return array_sum($this->totals);
    }
    public function getGroupTotals() {
        $totals = [];
        for($j = 0; $j < count($this->counts[0]); $j++){
            $totals[$j] = 0;
            for($i = 0; $i < count($this->counts); $i++){
                $totals[$j] += $this->counts[$i][$j];
            }
        }
        return $totals;
    }

    public function getCountArray(){
        $arr = [];
        foreach($this->answers as $answer)
            $arr[] = $answer->getCountArray();
        return $arr;
    }
    public function getPercentArray(){
        $arr = [];
        foreach($this->answers as $answer)
            $arr[] = $answer->getPercentArray();
        return $arr;
    }

    public function getLabels(){
        return $this->labels;
    }

    public function getMainTotals() {
        $arr = [];
        foreach($this->answers as $answer)
            $arr[] = array_sum($answer->counts);
        return $arr;
    }

    public function getCategoryDivisors() {
        $divisors = [];
        foreach($this->answers as $answer) {
            $divisors[] = array_sum($answer->totals);
        }
        return $divisors;
    }

    public function fill($dbobj)
    {
        $this->autoid = isset($dbobj->autoid) ? $dbobj->autoid : null;
        $this->code = isset($dbobj->code) ? $dbobj->code : null;
        $this->question = isset($dbobj->question) ? $dbobj->question : null;
        $this->summary = isset($dbobj->summary) ? $dbobj->summary : null;
        $this->category = isset($dbobj->category) ? $dbobj->category : null;
        $this->lowCutoff = isset($dbobj->low_cutoff) ? $dbobj->low_cutoff : null;
        $this->highCutoff = isset($dbobj->high_cutoff) ? $dbobj->high_cutoff : null;
        $this->totalCutoff = isset($dbobj->total_cutoff) ? $dbobj->total_cutoff : null;
    }
}