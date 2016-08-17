<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

$ds = new DataService();
$cat = isset($_GET['cat'])? $_GET['cat'] : 1;
$grp = isset($_GET['grp'])? $_GET['grp'] : 'none';

if($cat == 1) {
    $title = "Substance Use";
    $qCodes = ['A2A','A3A','A4','D2A'];
    $labels = ['Lifetime Alcohol','Past-Month Alcohol','Binge Drinking','Lifetime Marijuana'];
    $lowCutoffs = [2,2,2,2];
    $highCutoffs = [null,null,null,null];
    $totalCutoffs = [null,null,null,null];
    $explanation = "<p>The Youth Survey asks about use of a wide variety of licit and illicit substances. The highlights page
        focuses on two of the most-commonly-used psychoactive substances among youth: alcohol (including data on binge drinking) and marijuana.</p>
        <p>To learn about other substances or to compare substance use with other behaviors, <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 2) {
    $title = "Sexual Activity";
    $qCodes = ['X1','X8'];
    $labels = ['Lifetime Sexual<br>Intercourse','Lifetime Oral Sex'];
    $lowCutoffs = [1,1];
    $highCutoffs = [1,1];
    $totalCutoffs = [null,null];
    $explanation = "<p>The Youth survey asks about students' sexual behavior, including preventive behaviors (condom use).
        Related questions addressing aggression in relationships are reported in the <a href='category.php?cat=5'>Dating Aggression</a> category.</p>
        <p>To learn about other sexual behaviors, <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 3) {
    $title = "Vehicle Safety";
    $qCodes = ['A5','S3'];
    $labels = ['Driving after Drinking','Texting while Driving'];
    $lowCutoffs = [3,3];
    $highCutoffs = [null,null];
    $totalCutoffs = [2,2];
    $explanation = "<p>The Youth Survey asks about behaviors that are associated with unsafe driving practices, such as driving
        after drinking and texting while driving..</p>
        <p>To learn more about vehicle safety, <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 4) {
    $title = "Bullying and Cyberbullying";
    $qCodes = ['B20','B22','CB3','CB2'];
    $labels = ['Bullied Someone<br>at School','Been Bullied at School','Cyberbullied<br>Someone at School','Been Cyberbullied<br>at School'];
    $lowCutoffs = [1,1,2,2];
    $highCutoffs = [1,1,null,null];
    $totalCutoffs = [null,null,null,null];
    $explanation = "<p>The Youth Survey asks questions about both bullying in-person and bullying online (called cyberbullying).</p>
        <p>Information specifically about bullying at school is available on the highlights page, while a broader range of activities (out-of-school behavior) is also available: <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 5) {
    $title = "Dating Aggression";
    $qCodes = ['B15','B25'];
    $labels = ['Partner Always Wants<br>to Know Whereabouts','Partner Physically<br>Forces Sex'];
    $lowCutoffs = [1,3];
    $highCutoffs = [1,null];
    $totalCutoffs = [null,2];
    $explanation = "<p>There are a variety of behaviors that might be classified as dating aggression, or that might signify a risk of dating aggression.
        These range from a partner physically forcing someone to have sexual intercourse to someone always wanting to know his or her partner’s whereabouts.</p>
        <p>To learn more about behaviors related to dating aggression, <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 6) {
    $title = "Other Aggression";
    $qCodes = ['B2A','B10A','W5'];
    $labels = ["Insulted Someone's<br>Race or Culture",'Had Race or<br>Culture Insulted','Carried a Weapon'];
    $lowCutoffs = [2,2,2];
    $highCutoffs = [null,null,null];
    $totalCutoffs = [null,null,null];
    $explanation = "<p>Aggression can take on a variety of forms, both verbal and physical. The highlights page provides information both on youth
        who had their race or culture insulted, and those who insulted others’ race or culture.  It also provides information on youth who carried a weapon.</p>
        <p>Data about additional behaviors or experiences indicating aggression are available: <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 7) {
    $title = "Physical Activity and Rest";
    $qCodes = ['H3','H3','H20','H1','H2'];
    $labels = ['One Hour of Physical Activity<br>at least 1 Day per Week','One Hour of Physical Activity<br>at least 5 Days per Week',
        'Eight or More Hours of Sleep','Watches TV for<br> 3+ Hours per Day','Uses Computer or Plays Video<br>Games for 3+ Hours per Day'];
    $lowCutoffs = [2,6,5,5,5];
    $highCutoffs = [null,null,null,null,null];
    $totalCutoffs = [null,null,null,null,null];
    $explanation = "<p>The Youth Survey provides data on a variety of interlinked health behaviors related to physical activity and rest.
        Highlights include both frequency of physical activity across selected timeframes as well as indicators of inactivity and information about adequate sleep.</p>
        <p>Additional data are available in this category by choosing to <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 8) {
    $title = "Nutrition";
    $qCodes = ['fruitveg','H7','RF31'];
    $labels = ['Ate Fruits and Vegetables<br>at least 5 Times per Day','Drank No Soda<br>during Past Week','Went Hungry at least Once<br>during Past Month'];
    $lowCutoffs = [5,1,3];
    $highCutoffs = [null,1,null];
    $totalCutoffs = [null,null,null];
    $explanation = "<p>The Youth Survey provides data on a variety of interlinked health behaviors related to physical activity and rest.
        Highlights include both frequency of physical activity across selected timeframes as well as indicators of inactivity and information about adequate sleep.</p>
        <p>Additional data are available in this category by choosing to <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 9) {
    $title = "Mental Health";
    $qCodes = ['M5','M1','M2'];
    $labels = ['High Stress','Felt Sad or Hopeless for<br>Two or More Weeks in a Row','Attempted Suicide'];
    $lowCutoffs = [8,1,1];
    $highCutoffs = [null,1,1];
    $totalCutoffs = [null,null,null];
    $explanation = "<p>The Youth Survey provides data about a variety of different aspects related to mental health. This page highlights
        students who reported high levels of stress, those who felt sad or helpless two or more weeks in a row (which may indicate risk for depression), and those who attempted suicide.</p>
        <p>Additional data on this topic are available at <a href='graphs.php'>Explore All Questions</a>.</p>";
}
else if($cat == 10) {
    $title = "Extracurricular Activities and Civic Behaviors";
    $qCodes = ['C13','C11','C12','C2'];
    $labels = ['Did Extra Curriculars<br>for 1+ Hour per Day','Did Homework<br>for 1+ Hour per Day','Went to Work<br>for 1+ hour per Day','Volunteered for<br>Community Service'];
    $lowCutoffs = [4,4,4,3];
    $highCutoffs = [null,null,null,null];
    $totalCutoffs = [null,null,null,null];
    $explanation = "<p>The Youth Survey asks about a variety of behaviors that indicate civic engagement or diligence, including
        completion of homework, working at a job, volunteering in the community, and participating in extracurricular activities.
        This page shows the percentage of students with a moderate level of engagement (1+ hour of work or at least one time volunteering).</p>
        <p>To see the exact levels of engagement of students, such as number of hours worked or number of times volunteered, <a href='graphs.php'>Explore All Questions</a>.</p>";
}

$finalPercents = []; //for each question, what percent of cases fell within the cutoff range
$finalCounts = [];

$isGrouped = $grp != 'none';
if($isGrouped) {
    $grouplabels = $ds->getLabels($grp);
}
else
    $grouplabels = ['Total'];

//get data for each question and combine at cutoff points
for($i=0; $i<count($qCodes); $i++)
{
    $rawCounts = $ds->getDataCutoff($qCodes[$i], $grp, $lowCutoffs[$i],$highCutoffs[$i]);
    $totals = $ds->getGroupTotalsCutoff($qCodes[$i], $grp, $totalCutoffs[$i]);
    $rawPercents = $ds->converToPercents($rawCounts,$totals,$isGrouped);

    $obj1 = ['answer' => $labels[$i]];
    $obj2 = ['answer' => $labels[$i]];

    //insert values into object
    for($j=0; $j<count($grouplabels); $j++)
    {
        $obj1['v'.$j] = $rawPercents[$j]['num'] * 100;
        $obj2['v'.$j] = $rawCounts[$j]['num'];
    }

    $finalPercents[] = $obj1;
    $finalCounts[] = $obj2;
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
    <link rel="stylesheet" href="css/app.css">
    <script>
        $(function() {
            createPercentChart(<?php echo json_encode($finalPercents); ?>, <?php echo json_encode($grouplabels); ?>,'', '',true);

            $('#grouping :input[value=<?php echo $grp;?>]').prop("checked",true);
            $('#grouping').buttonset();
            $('#grouping :input').click(function() {
                window.location = "category.php?cat=<?php echo $cat;?>&grp="+this.value;
            });
            $( "#freqtabs" ).tabs();

            $("#btnExport").click(function (e) {
                window.open('data:application/vnd.ms-excel,' + $('#datatable').html().replace(/ /g, '%20'));
                e.preventDefault();
            });
        });

        var tableToExcel = (function () {
            var uri = 'data:application/vnd.ms-excel;base64,'
                , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
                , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
                , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
            return function (table, name, filename) {
                if (!table.nodeType) table = document.getElementById(table)
                var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML }

                document.getElementById("dlink").href = uri + base64(format(template, ctx));
                document.getElementById("dlink").download = filename;
                document.getElementById("dlink").click();

            }
        })();
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="h2 shadowdeep">Select a Category</div>
                <ul class="categories shadow">
                    <li><a href='?cat=1'>Substance Use</a></li>
                    <li><a href='?cat=2'>Sexual Activity</a></li>
                    <li><a href='?cat=3'>Vehicle Safety</a></li>
                    <li><a href='?cat=4'>Bullying and Cyberbullying</a></li>
                    <li><a href='?cat=5'>Dating Aggression</a></li>
                    <li><a href='?cat=6'>Other Aggressive Behaviors</a></li>
                    <li><a href='?cat=7'>Physical Activity and Rest</a></li>
                    <li><a href='?cat=8'>Nutrition and Weight Loss Behaviors</a></li>
                    <li><a href='?cat=9'>Mental Health</a></li>
                    <li><a href='?cat=10'>Extracurricular Activities and Civic Behaviors</a></li>
                </ul>
        </div>
        <div class="col-md-9 mainbar">
            <div style="text-align: center;">
                <h3><?php echo $title;?></h3>
                <?php echo $explanation;?>
            </div>

            <div id="grouping" class="groupbox" style="width:500px; margin: 20px auto 0">
                <label class="searchLabel">Group data by:</label>
                <input id="none" name="grouping" type="radio" value="none" checked="checked"/><label for="none">None</label>
                <input id="grade" name="grouping" type="radio" value="I2"/><label for="grade">Grade</label>
                <input id="gender" name="grouping" type="radio" value="I3"/><label for="gender">Gender</label>
                <input id="race" name="grouping" type="radio" value="race_eth"/><label for="race">Race</label>
            </div>
            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>



            <div style="text-align: center;">
                <h4>Cross-tabulated Frequencies</h4>
                <div id="freqtabs" style="display: inline-block;">
                    <ul>
                        <li><a href="#freqtabs-1">Percents</a></li>
                        <li><a href="#freqtabs-2">Counts</a></li>
                    </ul>
                    <div id="freqtabs-1">
                        <table id="datatable-count" class="datatable" style="margin: 0 auto; font-size:10pt;">
                            <tr>
                                <th colspan="<?php echo count($grouplabels)+1;?>" style="text-align: center;"><?php echo $title;?></th>
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
                        <a id="dlink"  style="display:none;"></a>
                        <input type="button" onclick="tableToExcel('datatable-count', 'name', 'fairfaxdata.xls')" value="Export to Excel">
                    </div>
                    <div id="freqtabs-2">
                        <table id="datatable-percent" class="datatable" style="margin: 0 auto; font-size:10pt;">
                            <tr>
                                <th colspan="<?php echo count($grouplabels)+1;?>" style="text-align: center;"><?php echo $title;?></th>
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
                        <a id="dlink"  style="display:none;"></a>
                        <input type="button" onclick="tableToExcel('datatable-percent', 'name', 'fairfaxdata.xls')" value="Export to Excel">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>