<?php
class Variable
{
	public $autoid;
	public $code;
	public $question;
	public $summary;
	public $category;
    public $answers;
    public $totals;
    public $connector;//word used in tooltip, "X% of students reported [connector] [question]"
	
	public function __construct()
	{
		$this->answers = array();
        $this->totals = array();
	}

	//after Answers have been constructed, set up each Answer's count array with a 0 value for each group
	public function initAnswers($groupVar){
        $numGroups = $groupVar == null ? 1 : count($groupVar->answers);
        foreach ($this->answers as $answer) {
            for ($i = 1; $i <= $numGroups; $i++) {
                $answer->addCount($i, 0);
                $answer->addPercent($i, 0);
                $this->totals[$i-1] = 0;
            }
        }
    }

	public function getAnswer($answerCode){
        return $this->answers[intval($answerCode)-1];
    }

    public function addAnswer($answerCode, $label){
        $code = intval($answerCode);
        $answer = new Answer();
        $answer->code = $code;
        $answer->label = $label;
        $this->answers[$code-1] = $answer;
    }

    public function getTotal($groupCode){
        return $this->totals[intval($groupCode)-1];
    }

    public function getSumTotal(){
        return array_sum($this->totals);
    }

    public function addTotal($groupCode, $num){
        $this->totals[intval($groupCode)-1] = floatval($num);
    }

    public function calculatePercents(){
        foreach ($this->answers as $answer){
            foreach ($answer->counts as $groupCode => $count){
                //total may be saved on Variable or on Answer
                if(count($this->totals) > 0)
                    $total = $this->totals[$groupCode];
                else
                    $total = $answer->totals[$groupCode];

                //don't divide by 0
                if($total == 0)
                    $answer->percents[$groupCode] = 0;
                else
                    $answer->percents[$groupCode] = $count / $total;
            }
        }
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
        $arr = [];
        foreach($this->answers as $answer)
            $arr[] = $answer->label;
        return $arr;
    }

    public function getGroupTotals() {
        return $this->totals;
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
}