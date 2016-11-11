<?php
require_once 'DataService.php';

//Get raw counts and percentages for the question(s) and filters chosen
function getRawData($q1, $grp, $grade, $gender, $race)
{
    $ds = new DataService();
    $mainVar = $ds->getVariableByCode($q1);
    if ($mainVar == null)
        die("User input was invalid.");
    $groupVar = $ds->getVariableByCode($grp);

    $filter = " 1 ";
    if (isset($grade))
        $filter .= " AND I2 = " . $ds->connection->real_escape_string($grade);
    if (isset($gender))
        $filter .= " AND I3 = " . $ds->connection->real_escape_string($gender);
    if (isset($race))
        $filter .= " AND race_eth = " . $ds->connection->real_escape_string($race);

    $rawCounts = $ds->getData($mainVar, $groupVar, $filter);               //i.e. [ ['answer'=>1, 'subgroup'=>3, 'num=>2133.23] ... ]
    $totals = $ds->getGroupTotals($mainVar, $groupVar, $filter);           //i.e. [ ['subgroup'=>3, 'num=>8954.13] ... ]
    $rawPercents = $ds->convertToPercents($rawCounts, $totals, $isGrouped);   //i.e. [ ['answer'=>1, 'subgroup'=>3, 'num=>0.1745] ... ]

    return [$rawCounts, $rawPercents, $totals];
}

//Restructure raw
function formatDataForGraph($q1, $grp, $isGrouped, $rawPercents, $rawCounts)
{
    $ds = new DataService();

    //get the array of answers to the question(s)
    $labels = $ds->getLabels($q1);
    if ($isGrouped) {
        $grouplabels = $ds->getLabels($grp);
    } else
        $grouplabels = ['Total'];

    $finalPercents = [];
    $finalCounts = [];

    //For each answer to the main question, create a new object
    //with a label and a value for each answer to the grouping question.
    for($i=0; $i<count($labels); $i++)
    {
        $obj1 = ['answer' => $labels[$i]];
        $obj2 = ['answer' => $labels[$i]];

        //insert values into object
        for($j=0; $j<count($grouplabels); $j++)
        {
            $answer = $labels[$i] == 'No Response' ? null : $i+1;
            $group = $grouplabels[$j] == 'No Response' ? null : $j+1;

            $num = $ds->findData($rawPercents,$answer,$group,$isGrouped);
            $obj1['v'.$j] = $num * 100;

            $num = $ds->findData($rawCounts,$answer,$group,$isGrouped);
            $obj2['v'.$j] = $num;
        }

        $finalPercents[] = $obj1;
        $finalCounts[] = $obj2;
    }
}

function getSumTotal($totals)
{
    $sumTotal = 0;
    foreach ($totals as $total) {
        $sumTotal += $total['num'];
    }
    return $sumTotal;
}







//height is (labels*(labels+spacing)*bar height + header height
$graphHeight = min(1200,max(600,(count($grouplabels)+1)*count($labels)*30+100));
$noresponse = $ds->getNoResponseCount($q1, $grp);