<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 5/1/2016
 * Time: 10:54 PM
 */
require_once 'DataService.php';

$ds = new DataService();

//$data = $ds->getDataCutoff('I2',2,2,0,'race');
//var_dump($data);

$data = $ds->getData('A4', 'B13');
var_dump($data);

$labels = $ds->getLabels('B24');
var_dump($labels);

//construct new data array using labels
$newdata = [];
for($i=1; $i<=count($labels); $i++)
{
    $label = $labels[$i-1];
    echo "For $i label is ";
    var_dump($label);

    //stop when no more labels
    if($label == '')
        break;

    //get counts from old data array
    $num = 0;
    foreach($data as $row) {
        if($row['answer'] == $i) {
            $num = $row['num'];
            break;
        }
    }

    echo "Num is $num";

    $newdata[] = ['answer'=>$label, 'num'=>$num];
}
//add No response if there are any null values
if($data[0]['answer'] == null)
    $newdata[] = ['answer'=>'No Response', 'num'=>$data[0]['num']];
var_dump($newdata);
