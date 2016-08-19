<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

$ds = new DataService();
$q1 = isset($_GET['q1'])? $_GET['q1'] : 'A4';
$grp = isset($_GET['grp'])? $_GET['grp'] : 'none';
$isGrouped = $grp != 'none';

$variables = $ds->getVariables();
$mainVar = null;
$groupVar = null;

//check that codes for the main variable and the grouping variable are valid
foreach($variables as $var) {
    if($var->code == $q1) {
        $mainVar = $var;
    }
    if($var->code == $grp) {
        $groupVar = $var;
    }
}

if($mainVar != null && ($groupVar != null || !$isGrouped))
{
    $labels = $ds->getLabels($q1);
    $labels[] = 'No Response';

    if($isGrouped) {
        $grouplabels = $ds->getLabels($grp);
    }
    else
        $grouplabels = ['Total'];

    //construct filter
    $filter = " 1 ";
    if(isset($_GET['grade']))
        $filter .= " AND I2 = " . $_GET['grade'];
    if(isset($_GET['gender']))
        $filter .= " AND I3 = " . $_GET['gender'];
    if(isset($_GET['race']))
        $filter .= " AND race_eth = " . $_GET['race'];

    $rawCounts = $ds->getData($q1, $grp, $filter);
    $totals = $ds->getGroupTotals($grp, $filter);
    $rawPercents = $ds->converToPercents($rawCounts,$totals,$isGrouped);

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
//height is (labels*(labels+spacing)*bar height + header height
$graphHeight = min(1200,max(600,(count($grouplabels)+1)*count($labels)*30+100));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
    <?php include_styles() ?>
    <script src="js/amcharts/amcharts.js" type="text/javascript"></script>
    <script src="js/amcharts/serial.js" type="text/javascript"></script>
    <script src="js/amcharts/plugins/export/export.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css">
    <script src="js/crosstab.js" type="application/javascript"></script>
    <script src="js/exporttable.js" type="text/javascript"></script>
    <script>
        $(function() {
            finalCounts = <?php echo json_encode($finalCounts); ?>;
            finalPercents = <?php echo json_encode($finalPercents); ?>;
            groupLabels = <?php echo json_encode($grouplabels); ?>;
            answerLables = <?php echo json_encode($labels); ?>;

            init(<?php echo json_encode($variables); ?>, <?php echo json_encode($q1); ?>, <?php echo json_encode($grp); ?>);
            createPercentChart(<?php echo json_encode($finalPercents); ?>, <?php echo json_encode($grouplabels); ?>,
                <?php echo json_encode($mainVar->summary); ?>, <?php echo json_encode($groupVar->summary); ?>,false);

            createVariablesByCategory($("#demo1"),$("#demo2"),99);
            createVariablesByCategory($("#alcohol1"),$("#alcohol2"),1);
            createVariablesByCategory($("#tobacco1"),$("#tobacco2"),12);
            createVariablesByCategory($("#drugs1"),$("#drugs2"),5);
            createVariablesByCategory($("#mental1"),$("#mental2"),9);
            createVariablesByCategory($("#school1"),$("#school2"),4);
            createVariablesByCategory($("#bullying1"),$("#bullying2"),2);
            createVariablesByCategory($("#sexual1"),$("#sexual2"),3);
            createVariablesByCategory($("#family1"),$("#family2"),11);
            createVariablesByCategory($("#community1"),$("#community2"),10);
            createVariablesByCategory($("#safety1"),$("#safety2"),13);
            createVariablesByCategory($("#physical1"),$("#physical2"),6);
            createVariablesByCategory($("#nutrition1"),$("#nutrition2"),7);
            createVariablesByCategory($("#perception1"),$("#perception2"),8);

            $( "#accordion1" ).accordion({
                collapsible: true,
                active: false,
                heightStyle: "content"
            });
            $( "#accordion2" ).accordion({
                collapsible: true,
                active: false,
                heightStyle: "content"
            });
            $( "#interpret" ).accordion({
                collapsible: true,
                active: false,
                heightStyle: "content"
            });
            $( "#freqtabs" ).tabs();
        });
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="h2 shadowdeep">1. Select a Question</div>
            <div id="accordion1" class="accordion">
                <h3>Demographics</h3><div id="demo1"></div>
                <h3>Alcohol</h3><div id="alcohol1"></div>
                <h3>Tobacco</h3><div id="tobacco1"></div>
                <h3>Drugs</h3><div id="drugs1"></div>
                <h3>Mental Health</h3><div id="mental1"></div>
                <h3>School</h3><div id="school1"></div>
                <h3>Bullying</h3><div id="bullying1"> </div>
                <h3>Sex and Relationships</h3><div id="sexual1"></div>
                <h3>Family</h3><div id="family1"></div>
                <h3>Community Support</h3><div id="community1"></div>
                <h3>Safety and Violence</h3><div id="safety1"></div>
                <h3>Physical Activity</h3><div id="physical1"></div>
                <h3>Nutrition</h3><div id="nutrition1"></div>
                <h3>Self/Peer Perception</h3><div id="perception1"></div>
            </div>

            <div class="h2 shadowdeep">2. (Optional) Compare to Another Question</div>
            <div id="accordion2" class="accordion">
                <h3>Demographics</h3><div id="demo2"></div>
                <h3>Alcohol</h3><div id="alcohol2"></div>
                <h3>Tobacco</h3><div id="tobacco2"></div>
                <h3>Drugs</h3><div id="drugs2"></div>
                <h3>Mental Health</h3><div id="mental2"></div>
                <h3>School</h3><div id="school2"></div>
                <h3>Bullying</h3><div id="bullying2"> </div>
                <h3>Sex and Relationships</h3><div id="sexual2"></div>
                <h3>Family</h3><div id="family2"></div>
                <h3>Community Support</h3><div id="community2"></div>
                <h3>Safety and Violence</h3><div id="safety2"></div>
                <h3>Physical Activity</h3><div id="physical2"></div>
                <h3>Nutrition</h3><div id="nutrition2"></div>
                <h3>Self/Peer Perception</h3><div id="perception2"></div>
            </div>

            <div class="h2 shadowdeep">3. (Optional) Filter Results by...</div>
            <div class="bordergrey filterbox" style="color: black">
                <label for="filteryear">Year: </label>
                <select id="filteryear">
                    <option value="0">All</option>
                    <option value="2015">2015</option>
                </select><br>
                <label for="filtergrade">Grade: </label>
                <select id="filtergrade">
                    <option value="0">All</option>
                    <option value="1">6th</option>
                    <option value="2">8th</option>
                    <option value="3">10th</option>
                    <option value="4">12th</option>
                </select><br>
                <label for="filtergrade">Gender: </label>
                <select id="filtergender">
                    <option value="0">All</option>
                    <option value="1">Female</option>
                    <option value="2">Male</option>
                </select><br>
                <label for="filterrace">Race: </label>
                <select id="filterrace">
                    <option value="0">All</option>
                    <option value="1">White</option>
                    <option value="2">Black</option>
                    <option value="3">Hispanic</option>
                    <option value="4">Asian/Pacific Islander</option>
                    <option value="5">Other/Multiple</option>
                </select><br>
                <input type="button" onclick="filter()" value="Filter" style="margin: 0px 0px 0px 103px; width: 100px;">
            </div>
        </div>

        <div class="col-md-9">
            <div style="text-align: center;">
                <h4><?php echo $mainVar->question;?></h4>
                <?php if($isGrouped) {
                    echo "<span style='font-style: italic;'>compared to</span><h4>$groupVar->question</h4>";
                }?>
                <?php if(strlen($filter) > 3) {
                    echo "<span style='font-style: italic;'>filtered by</span><h4>";
                    if(isset($_GET['grade']))
                        echo "Grade ";
                    if(isset($_GET['gender']))
                        echo "Gender ";
                    if(isset($_GET['race']))
                        echo "Race/Ethnicity ";
                    echo "</h4>";
                }?>
            </div>


            <?php if($isGrouped) { ?>
            <div id="interpret" class="accordion" style="width: 70%; margin: 0 auto; padding: 10px 0;">
                <h3>How to interpret this graph</h3>
                <div style="font-size:10pt">
                    <p>Out of all of the students who answered <b><?php echo $grouplabels[0];?></b> to <b><?php echo $groupVar->summary;?></b>, the <span style="color: #70a1c2; font-weight: bold">blue bar</span> shows what percentage choose each answer to <b><?php echo $mainVar->summary;?></b>.</p>
                    <p>For example, out of all of the students who answered <?php echo $grouplabels[0];?> to <?php echo $groupVar->summary;?>, <?php echo number_format($finalPercents[0]['v0'],1);?>% of them answered <?php echo $labels[0];?> to <?php echo $mainVar->summary;?>, and <?php echo number_format($finalPercents[1]['v0'],1);?>% of them answered <?php echo $labels[1];?>.</p>
                    <p>Each color's bars should add up to 100%.</p>
                </div>
            </div>
            <?php } ?>

            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h4>Cross-tabulated Frequencies</h4>
                <div id="freqtabs" style="display: inline-block;">
                    <ul>
                        <li><a href="#freqtabs-1">Percents</a></li>
                        <li><a href="#freqtabs-2">Counts</a></li>
                    </ul>
                    <div id="freqtabs-1">
                        <table id="datatable-count" class="datatable" style="margin: 0 auto; font-size:10pt;">
                            <tr>
                                <th rowspan="<?php echo count($labels)+2;?>" style="width: 80px;"><?php echo $mainVar->summary;?></th>
                                <?php if($isGrouped) { ?><th colspan="<?php echo count($grouplabels)+1;?>"><?php echo $groupVar->summary;?></th><?php }?>
                            </tr>
                            <tr>
                                <th></th>
                                <?php foreach($grouplabels as $label) {
                                    echo "<th>$label</th>";
                                }?>
                            </tr>
                            <?php for($i=0; $i<count($finalPercents); $i++) {
                                echo "<tr>";
                                for($j=0; $j<count($finalPercents[$i]); $j++) {
                                    if($j == 0) {
                                        $val = $finalPercents[$i]['answer'];
                                        echo "<th>$val</th>";
                                    }
                                    else {
                                        $val = number_format($finalPercents[$i]['v'.($j-1)], 1);
                                        echo "<td>$val%</td>";
                                    }
                                }
                                echo "</tr>";
                            }?>
                        </table>
                        <input type="button" onclick="tableToExcel(true)" value="Export to CSV">
                    </div>
                    <div id="freqtabs-2">
                        <table id="datatable-percent" class="datatable" style="margin: 0 auto; font-size:10pt;">
                            <tr>
                                <th rowspan="<?php echo count($labels)+2;?>" style="width: 80px;"><?php echo $mainVar->summary;?></th>
                                <?php if($isGrouped) { ?><th colspan="<?php echo count($grouplabels)+1;?>"><?php echo $groupVar->summary;?></th><?php }?>
                            </tr>
                            <tr>
                                <th></th>
                                <?php foreach($grouplabels as $label) {
                                    echo "<th>$label</th>";
                                }?>
                            </tr>
                            <?php for($i=0; $i<count($finalCounts); $i++) {
                                echo "<tr>";
                                for($j=0; $j<count($finalCounts[$i]); $j++) {
                                    if($j == 0) {
                                        $val = $finalCounts[$i]['answer'];
                                        echo "<th>$val</th>";
                                    }
                                    else {
                                        $val = number_format($finalCounts[$i]['v'.($j-1)], 0);
                                        echo "<td>$val</td>";
                                    }
                                }
                                echo "</tr>";
                            }?>
                        </table>
                        <input type="button" onclick="tableToExcel(false)" value="Export to CSV">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>