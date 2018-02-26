<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/TrendGroups.php';

//Process user input
$trendGroup = isset($_GET['group'])? $_GET['group'] : null;
$questionCode = isset($_GET['question'])? $_GET['question'] : null;
$grade = isset($_GET['grade']) ? $_GET['grade'] : null;
$gender = isset($_GET['gender']) ? $_GET['gender'] : null;
$race = isset($_GET['race']) ? $_GET['race'] : null;

$ds = DataService::getInstance(2015, DataService::EIGHT_TO_TWELVE);
$variables = $ds->getTrendVariables();

$showIntro = $trendGroup == null && $questionCode == null;
if(!$showIntro)
{
    //Set up variables (either single question or group)
    $variablesInGraph = [];
    if($questionCode != null) {
        $variable = $ds->getCutoffVariable($questionCode);
        $graphName = $variable->summary;
        $variablesInGraph[] = $variable;
    }
    else {
        $groupCodes = getGroupCodes($trendGroup);
        $graphName = "Trend Group: " . getGroupName($trendGroup);
        foreach ($groupCodes as $code) {
            $variable = $ds->getCutoffVariable($code);
            $variablesInGraph[] = $variable;
        }
    }

    //Get data for each year
    $years = getAllYears(); //from config.php
    $percentData = [];
    $filter = $ds->createFilterString($grade, $gender, $race);
    foreach ($years as $year) {
        $ds = DataService::getInstance($year, DataService::EIGHT_TO_TWELVE);
        $yearData = ["year" => $year];
        for($i=0; $i<count($variablesInGraph); $i++) {
            $ds->getCutoffPositives($variablesInGraph[$i], null, $filter);
            $ds->getCutoffTotal($variablesInGraph[$i], null, $filter);
            $yearData['v'.$i] = round($variablesInGraph[$i]->getPercent(1) * 100, 1);
        }
        $percentData[] = $yearData;
    }

    //get labels and counts for data table
    $labels = [];
    $tooltips = [];
    foreach ($variablesInGraph as $variable) {
        $labels[] = $variable->summary;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trends - Fairfax County Youth Survey</title>
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
            var trendGroup = <?php echo json_encode($trendGroup); ?>;
            var questionCode = <?php echo json_encode($questionCode); ?>;
            var grade = <?php echo json_encode($grade); ?>;
            var gender = <?php echo json_encode($gender); ?>;
            var race = <?php echo json_encode($race); ?>;

            //persist user inputs in search form
            var groupSelect = $("#group");
            var questionSelect = $("#question");
            enableSelect2(variables, null, "#question"); //must come before setting question value
            questionSelect.val(questionCode);
            questionSelect.trigger('change');
            groupSelect.val(trendGroup);
            $('#filtergrade').val(grade);
            $('#filtergender').val(gender);
            $('#filterrace').val(race);

            //If Group is selected, make Question blank and vice versa
            var blockEvent = false;
            groupSelect.change(function () {
                blockEvent = true;
                questionSelect.val('');
                questionSelect.trigger('change');
                blockEvent = false;
            });
            questionSelect.change(function () {
                if(!blockEvent)
                    $("#group").val('');
            });

            <?php if(!$showIntro): ?>
            mainTitle = <?php echo json_encode($graphName); ?>;
            labels = <?php echo json_encode($labels); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            questions = <?php echo json_encode($variablesInGraph); ?>;
            years = <?php echo json_encode($years); ?>;
            isGrouped = <?php echo json_encode($trendGroup != null); ?>;

            chart = createLineChart(percentData, labels);

            filterString = makeFilterString(grade, gender, race);
            var titleString = "<h4>"+mainTitle+"</h4>";
            if(filterString != null)
                titleString += "<i>" + filterString + "</i>";
            $("#graphTitle").html(titleString);

            simpleTrendTable($('#datatable'), labels, years, percentData);
            <?php endif; ?>
        });
        function exportCSV() {
            var title = isGrouped ? mainTitle : "Trends: "+mainTitle;
            simpleTrendCSV(title, labels, years, percentData, filterString);
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, null, years[0]+' to '+years[years.length-1], filterString);
        }
        function searchData() {
            var group = $("#group").val();
            var question = $("#question").val();
            var category = $('#category').val();
            var grade = $("#filtergrade").val();
            var gender = $("#filtergender").val();
            var race = $("#filterrace").val();
            var url = '';

            if(group != '')
                url = 'trends.php?group='+group;
            else if(question != '') {
                url = 'trends.php?question=' + question;
                if(category != '')
                    url += '&cat='+category;
            }
            else
                return;//if both are blank, do nothing

            if(grade != '')
                url += "&grade="+grade;
            if(gender != '')
                url += "&gender="+gender;
            if(race != '')
                url += "&race="+race;

            window.location.href = url;
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row" style="background-color: #2e6da4;">
        <div class="searchbar">
            <label class="shadow" style="width: 250px" for="group">1. Select a group of questions:</label>
            <select id="group" style="width:260px; margin-bottom: 0px" class="selector">
                <option value="">Select an option</option>
                <option value="1">Alcohol</option>
                <option value="2">Tobacco</option>
                <option value="3">Drugs</option>
                <option value="4">Sexual Health</option>
                <option value="5">Vehicle Safety</option>
                <option value="6">Bullying & Cyberbullying</option>
                <option value="7">Dating Aggression</option>
                <option value="8">Harassment and Aggressive Behaviors</option>
                <option value="10">Nutrition and Physical Activity</option>
                <option value="11">Mental Health</option>
                <option value="12">Civic Engagement and Time Use</option>
                <option value="13">Assets that Build Resiliency</option>
            </select><br>
            <label class="shadow" style="width: 250px" for="question">OR Select an individual question:</label>
            <select id="question" style="width:300px" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 20px; width: 250px">2. (Optional) Filter data by:</label>
            <select id="filtergrade" class="filter selector" title="Grade">
                <option value="">Grade</option>
                <option value="1">8th</option>
                <option value="2">10th</option>
                <option value="3">12th</option>
            </select>
            <select id="filtergender" class="filter selector" title="Gender">
                <option value="">Gender</option>
                <option value="1">Female</option>
                <option value="2">Male</option>
            </select>
            <select id="filterrace" class="filter selector" title="Race">
                <option value="">Race</option>
                <option value="1">White</option>
                <option value="2">Black</option>
                <option value="3">Hispanic</option>
                <option value="4">Asian/Pacific Islander</option>
                <option value="5">Other/Multiple</option>
            </select><br>
            <div style="text-align: center;">
                <input type="button" value="Generate Graph" class="btn" onclick="searchData()">
                <input type="button" value="Reset" class="btn" onclick="location.href = 'trends.php'">
            </div>
        </div>
    </div>
    <div class="row" style="margin: 10px auto; max-width: 1400px">
        <?php if($showIntro):
            include "trends-instructions.php";
        else: ?>
            <div style="text-align: center;">
                <div id="graphTitle"></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100; margin-right: 80px">
            </div>

            <div id="chartdiv" style="width100%; height:700px;"></div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the percentage of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <input type="button" onclick="exportCSV()" value="Export to CSV" class="btn btn-blue" style="margin-top: 10px">
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>