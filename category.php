<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/CategoryData.php';

$ds = new DataService();
$cat = isset($_GET['cat'])? $_GET['cat'] : 1;
$grp = isset($_GET['grp'])? $_GET['grp'] : 'none';

$mainVar = getCategoryVariable($cat);
$groupVar = $ds->getVariableByCode($grp);

//get data for each question
foreach($mainVar->answers as $answer)
{
    $ds->getDataCutoff($answer, $groupVar);
    $ds->getGroupTotalsCutoff($answer, $groupVar);
}
$mainVar->calculatePercents();

//Group variables
if($groupVar != null){
    $groupLabels = $groupVar->getLabels();
    $groupSummary = $groupVar->summary;
    $groupQuestion = $groupVar->question;
}
else {
    $groupLabels = ['Total'];
    $groupSummary = null;
    $groupQuestion = null;
}
$sumTotal = $mainVar->getSumTotal();
$graphHeight = min(1200,max(600,(count($groupLabels)+1)*count($mainVar->getLabels())*30+100));//height is (labels*(labels+spacing)*bar height + header height
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
    <?php include_styles() ?>
    <script src="js/amcharts/amcharts.js"></script>
    <script src="js/amcharts/serial.js"></script>
    <script src="js/amcharts/plugins/export/export.min.js"></script>
    <script src="js/crosstab.js"></script>

    <script>
        $(function() {
            mainQuestion = "Highlights: " + <?php echo json_encode($mainVar->question); ?>;
            groupQuestion = <?php echo json_encode($groupQuestion); ?>;
            mainTotals = <?php echo json_encode($mainVar->getMainTotals()); ?>;
            categoryDivisors = <?php echo json_encode($mainVar->getCategoryDivisors()); ?>;
            groupTotals = <?php echo json_encode($mainVar->getGroupTotals()); ?>;
            sumTotal = <?php echo json_encode($mainVar->getSumTotal()); ?>;
            connector = <?php echo json_encode($mainVar->connector); ?>;

            createPercentChart(<?php echo json_encode($mainVar->getCountArray()); ?>, <?php echo json_encode($mainVar->getPercentArray()); ?>,
                <?php echo json_encode($mainVar->getLabels()); ?>, <?php echo json_encode($groupLabels); ?>,
                <?php echo json_encode($mainVar->question); ?>,  <?php echo json_encode($groupSummary); ?>,
                true, <?php echo json_encode($mainVar->tooltips); ?>);

            if(groupLabels.length == 1)
                createSimpleTable($('#datatable'));
            else
                createCrosstabTable($('#datatable'));

            $("#graphTitle").html(mainQuestion);
            $('#grouping :input[value=<?php echo $grp;?>]').prop("checked",true);
            $('#grouping').buttonset();
            $('#grouping :input').click(function() {
                window.location = "category.php?cat=<?php echo $cat;?>&grp="+this.value;
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <script src="js/exportgraph.js"></script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="h2 shadowdeep">Select a Category
                <div class="tipbutton"  data-toggle="tooltip" data-placement="top" title="Each category highlights several significant behaviors and shows the percentage of students that engaged in those behaviors."></div>
            </div>
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
                <h2 id="graphTitle"></h2>
                <div id="explanation" style="max-width:800px; margin: 0 auto"><?php echo $mainVar->explanation;?></div>
                <p><b>Mouse over</b> the graph's labels and bars to see in more detail what each element represents.</p>
            </div>

            <div id="grouping" class="groupbox" style="width:500px; margin: 20px auto 0">
                <label class="searchLabel">Group data by:</label>
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
                <input type="button" onclick="tableToExcel()" class="btn btn-blue" value="Export to CSV" style="margin-top: 10px">
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>