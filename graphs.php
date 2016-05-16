<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

$ds = new DataService();
$q1 = isset($_GET['q1'])? $_GET['q1'] : 'A4';
$grp = isset($_GET['grp'])? $_GET['grp'] : 'none';
$isGrouped = $grp != 'none';

$variables = $ds->getVariables();
$newdata = [];
$mainTitle = "";
$groupTitle = "";

//check that codes for the main variable and the grouping variable are valid
$mainvalid = false;
$groupvalid = !$isGrouped;
foreach($variables as $var) {
    if($var->code == $q1) {
        $mainvalid = true;
        $mainTitle = $var->question;
    }
    if($var->code == $grp) {
        $groupvalid = true;
        $groupTitle = $var->question;
    }
}

if($mainvalid && $groupvalid) {
    $labels = $ds->getLabels($q1);
    if($isGrouped)
        $grouplabels = $ds->getLabels($grp);
    else
        $grouplabels = ['Total'];

    $data = $ds->getData($q1, $grp);
    $newdata = [];

    //For each answer to the main question, create a new object
    //with a label and a value for each answer to the grouping question.
    //e.g. [ ['answer' => label1, 'v1' => num1-1, 'v2' => num1-2, ... ],
    //       ['answer' => label2, 'v1' => num2-1, 'v2' => num2-2, ... ], ... ]
    //num1-2 = number of students who chose 1st answer to main question and chose 2nd answer to grouping question
    for($i=1; $i<=count($labels); $i++)
    {
        $label = $labels[$i-1];

        //find this label in the data
        $index = 0;
        for($j=0; $j<count($data); $j++)
            if($data[$j]['answer'] == $i) {
                $index = $j;
                break;
            }


        $obj = ['answer' => $label];

        //insert values into object
        for($j=0; $j<count($grouplabels); $j++) {
            $obj['v'.$j] = $data[$index+$j]['num'] * 100;
        }


        $newdata[] = $obj;

        /*if($grp == 'I2') {
            $newdata[] = ['answer' => $label, 'grade8' => $data[$j]['num'] * 100, 'grade10' => $data[$j+1]['num'] * 100, 'grade12' => $data[$j+2]['num'] * 100];
        }
        else if($grp == 'I3') {
            //$newdata[] = ['answer' => $label, 'female' => $data[$j+1]['num'] * 100, 'male' => $data[$j+2]['num'] * 100, 'unknown' => $data[$j]['num'] * 100];
            $newdata[] = ['answer' => $label, 'female' => $data[$j]['num'] * 100, 'male' => $data[$j+1]['num'] * 100];
        }
        else if($grp == 'race') {
            $newdata[] = ['answer' => $label, 'white' => $data[$j]['num'] * 100, 'nonwhite' => $data[$j+1]['num'] * 100];
        }
        else {
            $newdata[] = ['answer'=>$label, 'total'=>$data[$j]['num']*100];
        }*/
    }

    //add No response if there are any null values
    /*if($data[0]['answer'] == null) {
        if($grp == 'I2') {
            $newdata[] = ['answer' =>'No Response', 'grade8' => $data[0]['num'] * 100, 'grade10' => $data[1]['num'] * 100, 'grade12' => $data[2]['num'] * 100];
        }
        else if($grp == 'I3') {
            //$newdata[] = ['answer' =>'No Response', 'female' => $data[1]['num'] * 100, 'male' => $data[2]['num'] * 100, 'unknown' => $data[0]['num'] * 100];
            $newdata[] = ['answer' =>'No Response', 'female' => $data[0]['num'] * 100, 'male' => $data[1]['num'] * 100];
        }
        else if($grp == 'race') {
            $newdata[] = ['answer' =>'No Response', 'white' => $data[0]['num'] * 100, 'nonwhite' => $data[1]['num'] * 100];
        }
        else {
            $newdata[] = ['answer'=>'No Response', 'total'=>$data[0]['num']*100];
        }
    }*/

}
//var_dump($newdata);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
    <?php include_styles() ?>
    <script src="js/amcharts/amcharts.js" type="text/javascript"></script>
    <script src="js/amcharts/serial.js" type="text/javascript"></script>
    <script src="js/crosstab.js" type="application/javascript"></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js'></script>
    <link rel='stylesheet' href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css'>
    <script>
        $(function() {
            init(<?php echo json_encode($variables); ?>);
            createPercentChart(<?php echo json_encode($newdata); ?>, <?php echo json_encode($grouplabels); ?>);

            createVariablesByCategory($("#alcohol"),1);
            createVariablesByCategory($("#bullying"),2);
            createVariablesByCategory($("#sexual"),3);
            //createVariablesByCategory($("#schoolacts"),'C');

            $( "#accordion" ).accordion({
                collapsible: true,
                active: false,
                heightStyle: "content"
            });

            $('#grouping :input[value=<?php echo $grp;?>]').prop("checked",true);
            $('#grouping').buttonset();
            $('#grouping :input').click(function() {
                window.location = "graphs.php?q1=<?php echo $q1;?>&grp="+this.value;
            });
        });
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container">
    <div class="h2" style="margin: 0 auto;">Graphs by Category</div>
    <div class="row">
        <div class="col-md-12">
            Click on a category to show all questions in that category.<br>
            Select a question to view data.<br>
            You may select a second question to crosstabulate with the first.
            <div id="accordion">
                <h3>Alcohol</h3>
                <div id="alcohol"></div>

                <h3>Bullying</h3>
                <div id="bullying"> </div>

                <h3>Sex and Relationships</h3>
                <div id="sexual"> </div>

                <h3>School Activities</h3>
                <div id="schoolacts"></div>
            </div>

            <input type="button" value="Start Over" onclick="window.location='graphs.php'">
            <div id="grouping" class="bordergrey" style="width:500px; margin: 0 auto">
                <label class="searchLabel">Group data by:</label>
                <input id="none" name="grouping" type="radio" value="none" checked="checked"/><label for="none">None</label>
                <input id="grade" name="grouping" type="radio" value="I2"/><label for="grade">Grade</label>
                <input id="gender" name="grouping" type="radio" value="I3"/><label for="gender">Gender</label>
                <input id="race" name="grouping" type="radio" value="race"/><label for="race">Race</label>
            </div>

            <div style="text-align: center; font-weight:bold; font-size:14pt;">
                <?php echo $mainTitle;
                if($isGrouped) {
                    echo "<br><span style='font-weight: normal; font-style: italic;'>separated by</span><br>$groupTitle";
                }?>
            </div>
            <div id="chartdiv" style="width100%; height:600px;"></div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>