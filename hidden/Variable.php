<?php
/**
 * Parent class of CutoffVariable and MultiVariable.
 * A Variable is a question that has some number of answers.
 */
abstract class Variable
{
	public $autoid;
	public $code; //identifier like I2 or A5
	public $question; //full text of the question
	public $summary; //short text
	public $category; //integer category i.e. Alcohol=1, Bullying=2

    public $counts;
    public $percents;
    public $totals;

    public $lowCutoff;
    public $highCutoff;
    public $totalCutoff;
    public $tooltip;

    public $labels = [];

    public function __construct()
    {
        $this->counts = array();
        $this->percents = array();
        $this->totals = array();
    }
}