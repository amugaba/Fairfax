<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/TrendGroups.php';

//Process user input
$trendGroup = isset($_GET['group'])? $_GET['group'] : null;
$category = isset($_GET['cat']) ? $_GET['cat'] : null;
$questionCode = isset($_GET['question'])? $_GET['question'] : null;
$grade = isset($_GET['grade']) ? $_GET['grade'] : null;
$gender = isset($_GET['gender']) ? $_GET['gender'] : null;
$race = isset($_GET['race']) ? $_GET['race'] : null;
$sexual_orientation = isset($_GET['so']) ? $_GET['so'] : null;

if(isset($_GET['year']))
    $year = intval($_GET['year']);
else
    $year = getCurrentYear();

if(isset($_GET['ds']) && $_GET['ds'] == '6th')
    $dataset = DataService::SIXTH;
else
    $dataset = DataService::EIGHT_TO_TWELVE;

$ds = DataService::getInstance($year, $dataset);
$variables = $ds->getTrendVariables(); //this would be all variable if we weren't showing trends

$showIntro = $trendGroup == null && $questionCode == null;
$threeToSucceedCode = "assets_3TS";
$variableAvailable = false;

if(!$showIntro) {
    //Set up variables (either single question or group)
    $variablesInGraph = [];
    $unavailableVariables = [];
    if ($questionCode != null) {
        $var = $ds->getCutoffVariable($questionCode);
        if (!$ds->isVariableInData($questionCode))
            $unavailableVariables[] = $var;
        else {
            $graphName = $var->summary;
            $variablesInGraph[] = $var;
        }
    } else {
        $groupCodes = getGroupCodes($trendGroup, $dataset);
        $graphName = "Trend Group: " . getGroupName($trendGroup);
        foreach ($groupCodes as $code) {
            $variable = $ds->getCutoffVariable($code);
            if (!$ds->isVariableInData($code))
                $unavailableVariables[] = $variable;
            else {
                $variablesInGraph[] = $variable;
            }
        }
    }
    $variableAvailable = count($variablesInGraph) > 0;
}
if($variableAvailable){
    $groupVar = $ds->getMultiVariable($threeToSucceedCode);
    $filter = $ds->createFilterString($grade, $gender, $race, $sexual_orientation);

    foreach ($variablesInGraph as $variable) {
        $variable->initializeCounts($groupVar);
        $ds->getCutoffPositives($variable, $groupVar, $filter);
        $ds->getCutoffTotal($variable, $groupVar, $filter);
    }

    //Create the data structure used by AmCharts for line graphs
    //[['answer' => 0, 'v0' => Variable0 percent, 'v1' => Variable1 percent, ...], ['answer' => 1, ...]]
    //where 'answer' is number 3TS assets
    $percentData = [];
    for ($i=0; $i < count($groupVar->labels); $i++) {
        $percentArray['answer'] = $groupVar->labels[$i];
        for($j = 0; $j < count($variablesInGraph); $j++) {
            $percentArray['v'.$j] = round($variablesInGraph[$j]->getPercent($i+1) * 100, 1);
        }
        $percentData[] = $percentArray;
    }

    //get labels and counts for data table
    $labels = [];
    $tooltips = [];
    foreach ($variablesInGraph as $variable) {
        $labels[] = $variable->summary;
    }
    $assetLabels = $groupVar->labels;
}

//Old code when not using cutoffs, remove after talking to Fairfax
/*if(!$showIntro) {
    //Get Variables
    $mainVar = $ds->getMultiVariable($questionCode);
    $groupVar = $ds->getMultiVariable($threeToSucceedCode);
    $variableAvailable = $ds->isVariableInData($questionCode);
}
if(!$showIntro && $variableAvailable)
{
    //Construct filter
    $filter = $ds->createFilterString($grade, $gender, $race);
    $graphName = $mainVar->summary;

    //Load data into main Variable
    $mainVar->initializeCounts($groupVar);
    $ds->getMultiPositives($mainVar, $groupVar, $filter);
    $ds->getMultiTotals($mainVar, $groupVar, $filter);
    $mainVar->calculatePercents();

    //Group variables
    if ($groupVar != null) {
        $groupLabels = $groupVar->getLabels();
        $groupSummary = $groupVar->summary;
        $groupQuestion = $groupVar->question;
    } else {
        $groupLabels = ['All Students'];
        $groupSummary = null;
        $groupQuestion = null;
    }

    //Create the data structure used by AmCharts for bar graphs
    //[['answer' => Var1 label, 'v0' => Group0 percent, 'v1' => Group1 percent, ...], ['answer' => Var 2 label, ...]]
    $percentData = [];
    for ($i=0; $i < count($groupVar->labels); $i++) {
        $percentArray['answer'] = $groupVar->labels[$i];
        for($j=0; $j<count($mainVar->labels); $j++) {
            $percentArray['v'.$j] = $mainVar->percents[$j][$i];
        }
        $percentData[] = $percentArray;
    }
}*/
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
        var year = <?php echo json_encode($year); ?>;
        $(function() {
            variables = <?php echo json_encode($variables); ?>;
            //get user inputs
            var trendGroup = <?php echo json_encode($trendGroup); ?>;
            var questionCode = <?php echo json_encode($questionCode); ?>;
            var category = <?php echo json_encode($category); ?>;
            var grade = <?php echo json_encode($grade); ?>;
            var gender = <?php echo json_encode($gender); ?>;
            var race = <?php echo json_encode($race); ?>;
            var sexOrientation = <?php echo json_encode($sexual_orientation); ?>;
            dataset = <?php echo json_encode($dataset); ?>;
            if(dataset === '6th') {
                $("#filtergrade").hide();
                $(".hide6").hide();
            }

            //persist user inputs in search form
            if(category != null)
                $('#category').val(category);
            var groupSelect = $("#group");
            var questionSelect = $("#question");
            enableSelect2(variables, "#category", "#question"); //must come before setting question value
            questionSelect.val(questionCode);
            questionSelect.trigger('change');
            groupSelect.val(trendGroup);
            $('#filteryear').val(year);
            $('#filtergrade').val(grade);
            $('#filtergender').val(gender);
            $('#filterrace').val(race);
            $('#filtersex').val(sexOrientation);
            $('#datasetSelect').val(dataset);

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

            <?php if($variableAvailable): ?>
            mainTitle = <?php echo json_encode($graphName); ?>;
            labels = <?php echo json_encode($labels); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            questions = <?php echo json_encode($variablesInGraph); ?>;
            isGrouped = <?php echo json_encode($trendGroup != null); ?>;
            assetLabels = <?php echo json_encode($assetLabels); ?>;

            chart = createLineChart(percentData, labels, 'Number of Assets');

            filterString = makeFilterString(grade, gender, race, sexOrientation);
            var titleString = "<h4>"+mainTitle+"</h4>";
            if(filterString != null)
                titleString += "<i>" + filterString + "</i>";
            $("#graphTitle").html(titleString);

            simpleTrendTable($('#datatable'), labels, assetLabels, percentData, 'Number of Assets');
            <?php endif; ?>
        });
        function exportCSV() {
            var title = isGrouped ? mainTitle : "Question: "+mainTitle;
            simpleTrendCSV(title, labels, assetLabels, percentData, year, dataset, filterString, 'Number of Assets');
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, null, year, dataset, filterString);
        }
        function searchData() {
            var group = $("#group").val();
            var year = $("#filteryear").val();
            var category = $("#category").val();
            var question = $("#question").val();
            var grade = $("#filtergrade").val();
            var gender = $("#filtergender").val();
            var race = $("#filterrace").val();
            var sexOrientation = $("#filtersex").val();

            if(group !== '')
                url = 'three-to-succeed.php?ds='+dataset+'&group='+group;
            else if(question !== '') {
                url = 'three-to-succeed.php?ds='+dataset+'&question=' + question;
            }
            else
                return;//if both are blank, do nothing

            url += '&year='+year;

            if(category != '')
                url += '&cat='+category;
            if(grade != '')
                url += "&grade="+grade;
            if(gender != '')
                url += "&gender="+gender;
            if(race != '')
                url += "&race="+race;
            if(sexOrientation != '')
                url += "&so="+sexOrientation;

            window.location.href = url;
        }
        function changeDataset() {
            window.location.href = "three-to-succeed.php?ds="+$('#datasetSelect').val()+"&year="+$("#filteryear").val();
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row title">
        <div class="shadow" style="font-size: 22px; margin-top: 15px; color: white; text-align: center">
            Using dataset
            <select id="datasetSelect" style="width:150px; height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset()" title="Change dataset drop down">
                <option value="8to12">8th-12th grade</option>
                <option value="6th">6th grade</option>
            </select>
            and year
            <select id="filteryear" style="height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset()" title="Change year drop down">
                <option value="2021">2021</option>
                <option value="2019">2019</option>
                <option value="2018">2018</option>
                <option value="2017">2017</option>
                <option value="2016">2016</option>
                <option value="2015">2015</option>
            </select>
        </div>
        <div class="searchbar" style="max-width: 840px">
            <label class="shadow" style="width: 414px" for="group">1. Select a group to see highlighted questions:</label>
            <select id="group" style="width:400px; margin-bottom: 0" class="selector">
                <option value="">Select an option</option>
                <option value="1">Alcohol</option>
                <option value="2">Tobacco</option>
                <option value="3">Drugs</option>
                <option value="20">Vaping</option>
                <option value="4" class="hide6">Sexual Health</option>
                <option value="5" class="hide6">Vehicle Safety</option>
                <option value="6">Bullying & Cyberbullying</option>
                <option value="7" class="hide6">Dating Aggression</option>
                <option value="8">Harassment and Aggressive Behaviors</option>
                <option value="10">Nutrition and Physical Activity</option>
                <option value="11">Mental Health</option>
                <option value="12">Civic Engagement and Time Use</option>
                <option value="13">Assets that Build Resiliency</option>
            </select><br>
            <label class="shadow" style="width: 250px" for="question">OR Select an <br>individual question:</label>
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
            </select>
            <select id="question" class="searchbox">
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
            </select>
            <select id="filtersex" class="filter selector" title="Sexual Orientation">
                <option value="">Sexual Orientation</option>
                <option value="1">Heterosexual</option>
                <option value="2">Gay or lesbian</option>
                <option value="3">Bisexual</option>
                <option value="4">Not sure</option>
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
        elseif(!$variableAvailable): ?>
            <div style="text-align: center; font-size: 18px">
                <p>The group or question you selected was not asked during the year you selected.<br>Please choose a different year or a different question.</p>
                <p><b>Question(s) not available this year:</b></p>
                <p>
                    <?php foreach ($unavailableVariables as $variable)
                        echo $variable->summary.'<br>'; ?>
                </p>
            </div>
        <?php else: ?>
            <div style="text-align: center;">
                <div id="graphTitle"></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right" class="hideIfNoGraph">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100; margin-right: 80px">
            </div>

            <div id="chartdiv" style="width100%; height:700px;"></div>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
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