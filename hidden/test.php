<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'DataService.php';
$ds = DataService::getInstance(2016, DataService::EIGHT_TO_TWELVE);

$cat = 1;
$grp = 'none';

//load data based on $cat
require_once 'CategoryData.php';

//set up variables
$var = new CutoffVariable();
$var->question = $title;
$var->explanation = $explanation;

for($i=0; $i<count($qCodes); $i++){
    $ans = new Answer();
    $ans->code = $qCodes[$i];
    $ans->label = $mainLabels[$i];
    $ans->tooltip = $tooltips[$i];
    $ans->lowCutoff = $lowCutoffs[$i];
    $ans->highCutoff = $highCutoffs[$i];
    $ans->totalCutoff = $totalCutoffs[$i];
    $var->answers[] = $ans;
}
$mainVar = $var;
$groupVar = $ds->getVariableByCode($grp);
var_dump($mainVar);

for($i=0; $i<count($mainVar->answers); $i++)
{
    $answer = $mainVar->answers[$i];
    $ds->getDataCutoff($answer, $groupVar);
    var_dump($answer);
    $ds->getGroupTotalsCutoff($answer, $groupVar);
    var_dump($answer);
}
*/