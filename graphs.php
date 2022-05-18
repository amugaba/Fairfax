<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

if(isset($_GET['year']))
    $year = intval($_GET['year']);
else
    $year = getCurrentYear();

if(isset($_GET['ds']) && $_GET['ds'] == '6th')
    $dataset = DataService::SIXTH;
else
    $dataset = DataService::EIGHT_TO_TWELVE;

$ds = DataService::getInstance($year, $dataset);
$variables = $ds->getVariables();

//Process user input
$q1 = isset($_GET['q1']) ? $_GET['q1'] : null;
$grp = isset($_GET['grp']) ? $_GET['grp'] : 'none';
$cat1 = isset($_GET['cat1']) ? $_GET['cat1'] : null;
$cat2 = isset($_GET['cat2']) ? $_GET['cat2'] : null;
$grade = isset($_GET['grade']) ? $_GET['grade'] : null;
$gender = isset($_GET['gender']) ? $_GET['gender'] : null;
$race = isset($_GET['race']) ? $_GET['race'] : null;
$sexual_orientation = isset($_GET['so']) ? $_GET['so'] : null;

$showIntro = $q1 == null;
if(!$showIntro) {
    //Get Variables
    $mainVar = $ds->getMultiVariable($q1);
    $groupVar = $ds->getMultiVariable($grp);
    if ($mainVar == null)
        die("User input was invalid.");

    $mainVariableAvailable = $ds->isVariableInData($q1);
    $groupVariableAvailable = $grp == 'none' || $ds->isVariableInData($grp);
}
if(!$showIntro && $mainVariableAvailable && $groupVariableAvailable) {
    $mainVar->initializeCounts($groupVar);
    //Construct filter
    $filter = $ds->createFilterString($grade, $gender, $race, $sexual_orientation);

    //Load data into main Variable
    $ds->getMultiPositives($mainVar, $groupVar, $filter);
    $ds->getMultiTotals($mainVar, $groupVar, $filter);
    $mainVar->calculatePercents();

    //Group variables
    if ($groupVar != null) {
        $groupLabels = $groupVar->getLabels();
        $groupSummary = $groupVar->summary;
        $groupQuestion = $groupVar->question;
    } else {
        $groupLabels = ['Total'];
        $groupSummary = null;
        $groupQuestion = null;
    }

    //Create the data structure used by AmCharts for bar graphs
    //[['answer' => Var1 label, 'v0' => Group0 percent, 'v1' => Group1 percent, ...], ['answer' => Var 2 label, ...]]
    $percentData = [];
    for ($i=0; $i < count($mainVar->labels); $i++) {
        $percentArray['answer'] = $mainVar->labels[$i];
        for($j=0; $j<count($groupLabels); $j++) {
            $percentArray['v'.$j] = $mainVar->percents[$i][$j];
        }
        $percentData[] = $percentArray;
    }

    $graphHeight = min(900, max(600, (count($groupLabels) + 1) * count($mainVar->getLabels()) * 30 + 100));//height is (labels*(labels+spacing)*bar height + header height
    $noresponse = $ds->getNoResponseCount($mainVar, $groupVar, $filter);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore the Data - Fairfax County Youth Survey</title>
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
            questions = <?php echo json_encode($variables); ?>;
            //get user inputs
            mainCode = <?php echo json_encode($q1); ?>;
            groupCode = <?php echo json_encode($grp); ?>;
            var grade = <?php echo json_encode($grade); ?>;
            var gender = <?php echo json_encode($gender); ?>;
            var race = <?php echo json_encode($race); ?>;
            var sexOrientation = <?php echo json_encode($sexual_orientation); ?>;
            var cat1 = <?php echo json_encode($cat1); ?>;
            var cat2 = <?php echo json_encode($cat2); ?>;
            year = <?php echo json_encode($year); ?>;
            dataset = <?php echo json_encode($dataset); ?>;
            if(dataset === '6th') {
                $(".hide6").hide();
            }

            //persist user inputs in search form
            if(cat1 != null)
                $('#category1').val(cat1);
            if(cat2 != null)
                $('#category2').val(cat2);

            enableSelect2(questions, "#category1", "#question1");
            enableSelect2(questions, "#category2", "#question2", true);

            if(mainCode != null) {
                $('#question1').val(mainCode);
                $("#question1").trigger('change');
            }
            if(groupCode != null && groupCode != 'none') {
                $('#question2').val(groupCode);
                $("#question2").trigger('change');
            }

            $('#filteryear').val(year);
            $('#filtergrade').val(grade);
            $('#filtergender').val(gender);
            $('#filterrace').val(race);
            $('#filtersex').val(sexOrientation);
            $('#datasetSelect').val(dataset);

            <?php if(!$showIntro && $mainVariableAvailable && $groupVariableAvailable): ?>
            mainTitle = <?php echo json_encode($mainVar->question); ?>;
            mainSummary = <?php echo json_encode($mainVar->summary); ?>;
            groupTitle = <?php echo json_encode($groupQuestion); ?>;
            groupSummary = <?php echo json_encode($groupSummary); ?>;
            mainLabels = <?php echo json_encode($mainVar->labels); ?>;
            groupLabels = <?php echo json_encode($groupLabels); ?>;
            counts = <?php echo json_encode($mainVar->counts); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            sumPositives = <?php echo json_encode($mainVar->getSumPositives()); ?>;
            totals = <?php echo json_encode($mainVar->totals); ?>;
            groupTotals = <?php echo json_encode($mainVar->getGroupTotals()); ?>;
            sumTotal = <?php echo json_encode($mainVar->getSumTotal()); ?>;
            isGrouped = groupLabels.length > 1;

            createBarGraph(percentData, mainTitle, groupTitle, groupLabels, null, mainSummary);

            if(!isGrouped)
                createSimpleExplorerTable($('#datatable'), mainLabels, counts, sumTotal);
            else
                createCrosstabExplorerTable($('#datatable'), mainSummary, groupSummary, mainLabels, groupLabels, counts, sumPositives, groupTotals, sumTotal);

            filterString = makeFilterString(grade, gender, race, sexOrientation);
            titleString = "<h4>"+year+"</h4><h4>"+mainTitle+"</h4>";
            if(isGrouped)
                titleString += "<i>compared to</i><h4>" + groupTitle + "</h4>";
            if(filterString != null)
                titleString += "<i>" + filterString + "</i>";
            $("#graphTitle").html(titleString);
            <?php endif; ?>

            $('[data-toggle="tooltip"]').tooltip();
        });
        function exportCSV() {
            if(!isGrouped)
                simpleExplorerCSV(mainTitle, mainLabels, counts, totals, year, dataset, filterString);
            else
                crosstabExplorerCSV(mainTitle, groupTitle, mainLabels, groupLabels, counts, sumPositives, groupTotals, sumTotal, filterString, year, dataset);
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, groupTitle, year, dataset, filterString);
        }

        function searchData() {
            var q1 = $('#question1').val();
            var q2 = $('#question2').val();
            var cat1 = $('#category1').val();
            var cat2 = $('#category2').val();
            var year = $("#filteryear").val();
            var grade = $("#filtergrade").val();
            var gender = $("#filtergender").val();
            var race = $("#filterrace").val();
            var sexOrientation = $("#filtersex").val();

            if(q1 != '') {
                var url = 'graphs.php?ds='+dataset+'&q1='+q1;

                if(q2 != '')
                    url += '&grp='+q2;
                if(cat1 != '')
                    url += '&cat1='+cat1;
                if(cat2 != '')
                    url += '&cat2='+cat2;
                if(year != '')
                    url += "&year="+year;
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
        }
        function changeDataset() {
            window.location.href = "graphs.php?ds="+$('#datasetSelect').val()+"&year="+$("#filteryear").val();
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
        <div class="searchbar">
            <label class="shadow" for="question1">1. Select primary question:</label>
            <select id="category1" style="width:160px" class="selector" title="Select category to filter primary question">
                <option value="" selected="selected">All categories</option>
                <option value="99">Demographics</option>
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
            <select id="question1" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" for="question2">2. (Optional) Separate data &nbsp; &nbsp; &nbsp; by another question:</label>
            <select id="category2" style="width:160px" class="selector" title="Select category to filter secondary question">
                <option value="" selected="selected">All categories</option>
                <option value="99">Demographics</option>
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
            <select id="question2" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 20px">3. (Optional) Filter data by:</label>
            <select id="filtergrade" class="filter selector hide6" title="Grade">
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
            <select id="filtersex" class="filter selector hide6" title="Sexual Orientation">
                <option value="">Sexual Orientation</option>
                <option value="1">Heterosexual</option>
                <option value="2">Gay or lesbian</option>
                <option value="3">Bisexual</option>
                <option value="4">Not sure</option>
            </select><br>
            <div style="text-align: center;">
                <input type="button" value="Generate Graph" class="btn" onclick="searchData()">
                <input type="button" value="Reset" class="btn" onclick="location.href = 'graphs.php'">
            </div>
        </div>
    </div>
    <div class="row" style="margin: 10px auto; max-width: 1400px">
        <?php if($showIntro):
            include "instructions.php";
        elseif(!$mainVariableAvailable || !$groupVariableAvailable): ?>
            <div style="text-align: center; font-size: 18px">
                <p>The variable you selected was not collected during the year you selected.<br>Please choose a different year or different variable.</p>
                <p><b>Variable(s) not available this year:</b>
                    <?php if(!$mainVariableAvailable) echo '<br>'.$mainVar->summary;
                    if(!$groupVariableAvailable) echo '<br>'.$groupVar->summary; ?></p>
            </div>
        <?php else: ?>
            <div style="text-align: center;">
                <div id="graphTitle"></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100">
            </div>

            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the number of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <div>No Reponse: <?php echo number_format($noresponse,0);?></div>
                <input type="button" onclick="exportCSV()" value="Export to CSV" class="btn btn-blue" style="margin-top: 10px">
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>