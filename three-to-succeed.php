<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/TrendGroups.php';

//Process user input
$category = $_GET['cat'] ?? null;
$questionCode = $_GET['question'] ?? null;
$grp = $_GET['grp'] ?? null;
$pyramid = ''; //$_GET['pyr'] ?? ''; Uncomment to re-enable

if($pyramid > 0 && $grp > 3)
    $grp = null;

$groupCode = match ($grp) {
    '1' => 'I2',
    '2' => 'gender_nb',
    '3' => 'race',
    '4' => 'race_eth',
    '5' => 'X9',
    '6' => 'I3A',
    '7' => 'disability_cat',
    default => null
};

if(isset($_GET['year']))
    $year = intval($_GET['year']);
else
    $year = getCurrentYear();
//nonbinary doesn't exist in < 2022 surveys
if($groupCode == "gender_nb" && $year < 2022)
    $groupCode = "I3";

if(isset($_GET['ds']) && $_GET['ds'] == '6th')
    $dataset = DataService::SIXTH;
else
    $dataset = DataService::EIGHT_TO_TWELVE;

$ds = DataService::getInstance($year, $dataset);
$variables = $ds->get3TSVariables();

$showIntro = $questionCode == null;
//$threeToSucceedCode = "assets_3TS";

if(!$showIntro) {
    $variable = $ds->getCutoffVariable($questionCode);
    $variableAvailable = $ds->isVariableInData($questionCode);
}
if(!$showIntro && $variableAvailable){
    $graphName = '"'.$variable->summary.'" by Number of Assets';
    $groupVar = $ds->getMultiVariable($groupCode);

    //Create the data structure used by AmCharts for line graphs
    //[['answer' => 0, 'v0' => Variable0 percent, 'v1' => Variable1 percent, ...], ['answer' => 1, ...]]
    //where 'answer' is number 3TS assets
    $percentData = [];
    $assetTotals = [];
    for ($assetNum = 0; $assetNum <= 6; $assetNum++) {
        $assetData = ["answer" => $assetNum];
        $filter = $ds->createFilterString(null, null, null, null, $pyramid, null, $assetNum+1); //add 1 b/c answer1 = 0, answer2 = 1
        $variable->initializeCounts($groupVar);
        $ds->getCutoffPositives($variable, $groupVar, $filter);
        $ds->getCutoffTotal($variable, $groupVar, $filter);

        for ($j = 0; $j < count($variable->counts); $j++)
            $assetData['v'.$j] = round($variable->getPercent($j+1) * 100, 1);
        $percentData[] = $assetData;
        $assetTotals[] = count($variable->totals) === 0 ? null : array_sum($variable->totals);
    }

    //get labels and counts for data table
    $labels = $groupVar ? $groupVar->getLabels() : [$variable->summary];
    $assetLabels = [0,1,2,3,4,5,6];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Three to Succeed - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
    <script src="js/amcharts3/amcharts.js"></script>
    <script src="js/amcharts3/serial.js"></script>
    <script src="js/amcharts3/plugins/export/export.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.full.js"></script>
    <script src="js/variableSelector.js"></script>
    <script src="js/graph.js"></script>
    <script src="js/datatable.js"></script>
    <script>
        $(function() {
            variables = <?php echo json_encode($variables); ?>;
            //get user inputs
            var questionCode = <?php echo json_encode($questionCode); ?>;
            var group = <?php echo json_encode($grp); ?>;
            var category = <?php echo json_encode($category); ?>;
            pyramid = <?php echo json_encode($pyramid); ?>;
            dataset = <?php echo json_encode($dataset); ?>;
            year = <?php echo json_encode($year); ?>;

            if(dataset === '6th')
                $(".hide6").hide();
            if(pyramid > 0)
                $(".notPyramid").hide();
            else
                $(".isPyramid").hide();

            //persist user inputs in search form
            if(category != null)
                $('#category').val(category);
            var questionSelect = $("#question");
            enableSelect2(variables, "#category", "#question"); //must come before setting question value
            questionSelect.val(questionCode);
            questionSelect.trigger('change');
            $('#datasetSelect').val(dataset);
            $('#pyramidSelect').val(pyramid);
            $('#groupSelect').val(group);
            $('#yearSelect').val(year);

            <?php if(!$showIntro && $variableAvailable): ?>
            mainTitle = <?php echo json_encode($graphName); ?>;
            labels = <?php echo json_encode($labels); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            assetLabels = <?php echo json_encode($assetLabels); ?>;
            assetTotals = <?php echo json_encode($assetTotals); ?>;

            chart = createLineChart(percentData, labels, 'Number of Assets');

            var titleString = "<h4>"+mainTitle+"</h4>";
            $("#graphTitle").html(titleString);

            simpleTrendTable($('#datatable'), labels, assetLabels, percentData, 'Number of Assets', assetTotals);
            <?php endif; ?>

            $('[data-toggle="tooltip"]').tooltip();
        });
        function exportCSV() {
            var title = "Question: "+mainTitle;
            simpleTrendCSV(title, labels, assetLabels, percentData, year, dataset, "", 'Number of Assets', pyramid, assetTotals);
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, null, year, dataset, "", pyramid);
        }
        function searchData() {
            let group = $("#groupSelect").val();
            var category = $("#category").val();
            var question = $("#question").val();

            if(question === '')
                return;//if blank, do nothing

            let url = 'three-to-succeed.php?ds='+dataset+"&year="+year+"&pyr="+pyramid+'&question=' + question;

            if(category !== '')
                url += '&cat='+category;
            if(group !== '')
                url += '&grp='+group;

            window.location.href = url;
        }
        function changeDataset() {
            window.location.href = "three-to-succeed.php?ds="+$('#datasetSelect').val()+"&year="+$("#yearSelect").val()+"&pyr="+$("#pyramidSelect").val();
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row title">
        <div class="dataset-controls shadow" style="font-size: 22px; margin-top: 15px; color: white; text-align: center">
            Dataset:
            <select id="datasetSelect" style="width:150px; height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset()" title="Change dataset drop down">
                <option value="8to12">8th-12th grade</option>
                <option value="6th">6th grade</option>
            </select>
            &nbsp;Year:
            <select id="yearSelect" style="height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset()" title="Change year drop down">
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
                <option value="2019">2019</option>
                <option value="2018">2018</option>
                <option value="2017">2017</option>
                <option value="2016">2016</option>
                <option value="2015">2015</option>
            </select>
            <!--&nbsp;Pyramid:
            <select id="pyramidSelect" class="selector" onchange="changeDataset()" title="Change pyramid drop down">
                <option value="">All</option>
                <?php for($i=1; $i<=25; $i++) {
                    echo "<option value='$i'>$i</option>";
                } ?>
            </select>
            <div class="tipbutton" style="margin-left:5px; position: absolute" data-toggle="tooltip" data-placement="top"
                 title="When a pyramid is selected, data can only be grouped by grade, gender, and race (simplified) to preserve anonymity."></div>-->
        </div>
        <div class="searchbar" style="max-width: 850px">
            <label class="shadow" style="width: 250px" for="question">1. Select a question:</label>
            <select id="category" style="width:160px" class="selector" title="Select category to filter primary question">
                <option value="" selected="selected">All categories</option>
                <option value="1">Alcohol</option>
                <option value="12">Tobacco</option>
                <option value="5">Drugs</option>
                <option value="20">Vaping</option>
                <option value="2">Bullying & Cyberbullying</option>
                <option value="14">Harassment</option>
                <option value="3" class="hide6">Dating Aggression</option>
                <option value="13">Other Aggressive Behaviors</option>
                <option value="17" class="hide6">Vehicle Safety</option>
                <option value="6">Physical Activity</option>
                <option value="7">Nutrition</option>
                <option value="19" class="hide6">Unhealthy Weight Loss Behaviors</option>
                <option value="9">Mental Health</option>
                <option value="18" class="hide6">Sexual Health</option>
                <option value="4">School</option>
                <option value="11">Family</option>
                <option value="10">Community Support</option>
                <option value="16">Civic Engagement</option>
                <option value="15">Time Use</option>
                <option value="8">Self/Peer Perception</option>
                <option value="21">Disability</option>
            </select>
            <select id="question" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 20px; width: 250px">2. (Optional) Group data by:</label>
            <select id="groupSelect" class="filter selector" title="Group data by">
                <option value="">None</option>
                <option value="1" class="hide6">Grade</option>
                <option value="2">Gender</option>
                <option value="3" class="isPyramid">Race (simplified)</option>
                <option value="4" class="notPyramid">Race/Ethnicity</option>
                <option value="5" class="notPyramid hide6">Sexual Orientation</option>
                <?php if($year >= 2021 && $dataset == '8to12') { ?><option value="6">Transgender Status</option><?php } ?>
                <?php if($year >= 2023) { ?><option value="7">Disability</option><?php } ?>
            </select><br>
            <div style="text-align: center;">
                <input type="button" value="Generate Graph" class="btn" onclick="searchData()">
                <input type="button" value="Reset" class="btn" onclick="location.href = 'three-to-succeed.php'">
            </div>
        </div>
    </div>
    <div class="row" style="margin: 10px auto; max-width: 1400px">
        <?php if($showIntro):
            include "three-to-succeed-instructions.php";
        else: ?>
            <div style="text-align: center;">
                <div id="graphTitle"></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right" class="hideIfNoGraph">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100; margin-right: 80px">
            </div>

            <div id="chartdiv" style="width100%; height:700px;"></div>
            <div style="text-align: center">
                <div class="grid" style="font-size: 14px; display: inline-block; max-width:850px; width:100%; padding: 10px 10px 0 10px; border-radius: 20px; border: 2px solid">
                    <div class="grid-half">
                        <p>Can Ask Parents for Help with Personal Problems</p>
                        <p>Performs Community Service Once a Month or More</p>
                        <p>Feels It Is Important to Accept Responsibility for Actions</p>
                    </div>
                    <div class="grid-half">
                        <p>Does Extracurricular Activities Once a Month or More</p>
                        <p>Teachers Recognize Good Work</p>
                        <p>Could Talk to Adults in Community about Something Important</p>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the percentage of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <?php if($groupCode == 'I3') { ?>
                    <p style="font-style: italic">*For Gender, the Non-Binary response option is only avaiable for the 2022 survey and later.<br>
                        As such, the <b>Total</b> here only includes students that answered Male or Female.<br>
                        To see the total for all students, set <b>Group Data By</b> to None.</p>
                <?php } else if($groupCode > 0 && $groupCode !== 'I2') { ?>
                    <p style="font-style: italic">*The <b>Total</b> here only includes students that answered the <b>Group Data By</b> question.<br>
                        To see the total for all students, set Group Data By to None.</p>
                <?php } ?>
                <?php if($questionCode === 'A5' || $questionCode === 'S3' || $questionCode === 'S4') { ?>
                    <p style="font-style: italic">*For Vehicle Safety questions, only 12th-grade students were asked.</p>
                <?php } ?>
                <input type="button" onclick="exportCSV()" value="Export to CSV" class="btn btn-blue" style="margin-top: 10px">
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>