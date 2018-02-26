<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/CategoryData.php';

if(isset($_GET['year']))
    $year = intval($_GET['year']);
else
    $year = getCurrentYear();

$ds = DataService::getInstance($year, DataService::EIGHT_TO_TWELVE);
$cat = isset($_GET['cat'])? $_GET['cat'] : 1;
$grp = isset($_GET['grp'])? $_GET['grp'] : null;

$highlightGroup = getHighlightGroup($cat);
$groupVar = $ds->getMultiVariable($grp);
$variablesInGraph = [];
$filter = "1";

//get data for each question
for($i = 0; $i < count($highlightGroup->codes); $i++)
{
    $variable = $ds->getCutoffVariable($highlightGroup->codes[$i]);
    $variable->initializeCounts($groupVar);
    $variable->summary = $highlightGroup->labels[$i];
    $ds->getCutoffPositives($variable, $groupVar, $filter);
    $ds->getCutoffTotal($variable, $groupVar, $filter);
    $variable->calculatePercents();
    $variablesInGraph[] = $variable;
}

//Create the data structure used by AmCharts for bar graphs
//[['answer' => Var1 label, 'v0' => Group0 percent, 'v1' => Group1 percent, ...], ['answer' => Var 2 label, ...]]
$percentData = [];
foreach ($variablesInGraph as $variable) {
    $percentArray['answer'] = $variable->summary;
    for($i=0; $i<count($variable->counts); $i++) {
        $percentArray['v'.$i] = $variable->percents[$i];
    }
    $percentData[] = $percentArray;
}

//Also create data for display in graph and table
$mainLabels = []; //labels for main variable
$counts = []; //[[var1 counts], [var2 counts], ...] where [var1 counts] = [group1, group2, ...]
$sumPositives = []; //sum positives/counts for a variable
$variableTotals = []; //sum valid cases for a variable
$tooltips = []; //mouse-over pop-ups to explain graph labels and bars

foreach ($variablesInGraph as $variable) {
    $mainLabels[] = $variable->summary;
    $counts[] = $variable->counts;
    $sumPositives[] = array_sum($variable->counts);
    $variableTotals[] = array_sum($variable->totals);
    $tooltips[] = $variable->tooltip;
}

//Group variable data in case it's null
if($groupVar != null){
    $groupLabels = $groupVar->getLabels();
    $groupSummary = $groupVar->summary;
    $groupTitle = $groupVar->question;
}
else {
    $groupLabels = ['Total'];
    $groupSummary = null;
    $groupTitle = null;
}

//height is (labels*(labels+spacing)*bar height + header height
$graphHeight = min(1200,max(600,(count($groupLabels)+1)*count($highlightGroup->codes)*30+100));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Highlights - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
    <script src="js/amcharts3/amcharts.js"></script>
    <script src="js/amcharts3/serial.js"></script>
    <script src="js/amcharts3/plugins/export/export.min.js"></script>
    <script src="js/graph.js"></script>
    <script src="js/datatable.js"></script>
    <script>
        $(function() {
            mainTitle = <?php echo json_encode($highlightGroup->title); ?>;
            groupTitle = <?php echo json_encode($groupTitle); ?>;
            groupSummary = <?php echo json_encode($groupSummary); ?>;
            mainLabels = <?php echo json_encode($mainLabels); ?>;
            groupLabels = <?php echo json_encode($groupLabels); ?>;
            counts = <?php echo json_encode($counts); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            sumPositives = <?php echo json_encode($sumPositives); ?>;
            totals = <?php echo json_encode($variableTotals); ?>;
            tooltips = <?php echo json_encode($tooltips); ?>;
            isGrouped = groupLabels.length > 1;

            //Inputs, used to set links
            year = <?php echo json_encode($year); ?>;
            category = <?php echo json_encode($cat); ?>;
            group = <?php echo json_encode($grp); ?>;

            createBarGraph(percentData, mainTitle, groupSummary, groupLabels, tooltips);

            if(!isGrouped)
                createSimpleHighlightTable($('#datatable'), mainLabels, counts, totals);
            else
                createCrosstabHighlightTable($('#datatable'), mainTitle, groupTitle, mainLabels, groupLabels, counts, sumPositives, totals);

            $("#graphTitle").html("Highlights: " + mainTitle);
            $('#grouping :input[value='+group+']').prop("checked",true);
            $('#yearSelect').val(year);
            $('#grouping').buttonset();
            $('#grouping :input').click(function() {
                window.location = "highlights.php?year="+year+"&cat="+category+"&grp="+this.value;
            });
            $('[data-toggle="tooltip"]').tooltip();

            //add year to each category link
            $('.categories li a').each(function(){
                $(this).attr('href',$(this).attr('href')+'&year='+year);
            });
        });
        function changeYear(year) {
            if(group != null)
                window.location = "highlights.php?year="+year+"&cat="+category+"&grp="+group;
            else
                window.location = "highlights.php?year="+year+"&cat="+category;
        }
        function exportCSV() {
            if(!isGrouped)
                simpleHighlightCSV(mainTitle, mainLabels, counts, totals, year);
            else
                crosstabHighlightCSV(mainTitle, groupTitle, mainLabels, groupLabels, counts, sumPositives, totals, year);
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, groupTitle, year, null);
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="shadowdeep" style="font-size: 18px; margin-top: 15px;">Showing highlights for
                <select id="yearSelect" style="width:85px; height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeYear(this.value)" title="Change Year drop down">
                    <option value="2016">2016</option>
                    <option value="2015">2015</option>
                </select>
            </div>
            <h1 class="shadowdeep">Select a Category
                <div class="tipbutton"  data-toggle="tooltip" data-placement="top" title="Each category highlights several significant behaviors and shows the percentage of students that engaged in those behaviors."></div>
            </h1>
                <ul class="categories shadow">
                    <li><a href='?cat=1'>Alcohol</a></li>
                    <li><a href='?cat=2'>Tobacco</a></li>
                    <li><a href='?cat=3'>Drugs</a></li>
                    <li><a href='?cat=4'>Sexual Health</a></li>
                    <li><a href='?cat=5'>Vehicle Safety</a></li>
                    <li><a href='?cat=6'>Bullying and Cyberbullying</a></li>
                    <li><a href='?cat=7'>Dating Aggression</a></li>
                    <li><a href='?cat=8'>Harassment and Aggressive Behaviors</a></li>
                    <li><a href='?cat=10'>Nutrition and Physical Activity</a></li>
                    <li><a href='?cat=11'>Mental Health</a></li>
                    <li><a href='?cat=12'>Civic Engagement and Time Use</a></li>
                    <li><a href='?cat=13'>Assets that Build Resiliency</a></li>
                </ul>
        </div>
        <div class="col-md-9 mainbar">
            <div style="text-align: center;">
                <h2 id="graphTitle"></h2>
                <div id="explanation" style="max-width:800px; margin: 0 auto"><?php echo $highlightGroup->explanation;?></div>
                <p><b>Mouse over</b> the graph's labels and bars to see in more detail what each element represents.</p>
            </div>

            <div id="grouping" class="groupbox" style="width:500px; margin: 20px auto 0">
                <span style="font-weight: bold">Group data by:</span>
                <input id="none" name="grouping" type="radio" value="none" checked="checked"/><label for="none">None</label>
                <input id="grade" name="grouping" type="radio" value="I2"/><label for="grade">Grade</label>
                <input id="gender" name="grouping" type="radio" value="I3"/><label for="gender">Gender</label>
                <input id="race" name="grouping" type="radio" value="race_eth"/><label for="race">Race</label>
                <div class="tipbutton" style="margin:0 0 3px 17px"  data-toggle="tooltip" data-placement="top" title="You can separate students by grade, gender, or race to see how each group answered."></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100">
            </div>
            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the number of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <input type="button" onclick="exportCSV()" class="btn btn-blue" value="Export to CSV" style="margin-top: 10px">
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>