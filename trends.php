<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/TrendData.php';

$cat = isset($_GET['cat'])? $_GET['cat'] : 1;

$years = getAllYears();
$percentData = [];

foreach ($years as $year) {
    $ds = DataService::getInstance($year, DataService::EIGHT_TO_TWELVE);
    $mainVar = getTrendVariable($cat);
    $groupVar = $ds->getVariableByCode('none');

    //get data for each question
    foreach ($mainVar->answers as $answer) {
        $ds->getDataCutoff($answer, $groupVar);
        $ds->getGroupTotalsCutoff($answer, $groupVar);
    }
    $mainVar->calculatePercents();
    $percents = $mainVar->getPercentArray();

    $yearData = ["year" => $year];
    foreach ($mainVar->answers as $answer) {
        $yearData[$answer->code] = round($answer->percents[0] * 100,1);
    }
    $percentData[] = $yearData;
}

/*
$showIntro = !isset($_GET['q1']);
if(!$showIntro) {
    //Process user input
    $q1 = $_GET['q1'];
    $grp = isset($_GET['grp']) ? $_GET['grp'] : 'none';
    $grade = isset($_GET['grade']) ? $ds->connection->real_escape_string($_GET['grade']) : null;
    $gender = isset($_GET['gender']) ? $ds->connection->real_escape_string($_GET['gender']) : null;
    $race = isset($_GET['race']) ? $ds->connection->real_escape_string($_GET['race']) : null;
    //$year = isset($_GET['year']) ? $ds->connection->real_escape_string($_GET['year']) : null;
    $cat1 = isset($_GET['cat1']) ? $ds->connection->real_escape_string($_GET['cat1']) : null;
    $cat2 = isset($_GET['cat2']) ? $ds->connection->real_escape_string($_GET['cat2']) : null;

    //Get Variables
    $mainVar = $ds->getVariableByCode($q1);
    $groupVar = $ds->getVariableByCode($grp);

    if ($mainVar == null)
        die("User input was invalid.");
    $mainVar->initAnswers($groupVar);

    //Construct filter
    $filter = " 1 ";
    if ($grade != null)
        $filter .= " AND I2 = $grade";
    if ($gender != null)
        $filter .= " AND I3 = $gender";
    if ($race != null)
        $filter .= " AND race_eth = $race";

    //Load data into main Variable
    $ds->getData($mainVar, $groupVar, $filter);
    $ds->getGroupTotals($mainVar, $groupVar, $filter);
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
    $graphHeight = min(1200, max(600, (count($groupLabels) + 1) * count($mainVar->getLabels()) * 30 + 100));//height is (labels*(labels+spacing)*bar height + header height
    $noresponse = $ds->getNoResponseCount($q1, $grp);
}*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
    <?php include_styles() ?>
    <script src="js/amcharts3/amcharts.js"></script>
    <script src="js/amcharts3/serial.js"></script>
    <script src="js/amcharts3/plugins/export/export.min.js"></script>
    <script src="js/linegraph.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.full.js"></script>
    <script>
        $(function() {
            questions = <?php echo json_encode($mainVar->answers); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            testLineChart();

            $("#graphTitle").html("Trends: " + <?php echo json_encode($mainVar->question); ?>);
            $("#category").val(<?php echo json_encode($cat); ?>);
        });
    </script>
    <script src="js/exportgraph.js"></script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row" style="background-color: #2e6da4;">
        <div class="searchbar">
            <label class="shadow">1. Select a category:</label>
            <select id="category" style="width:260px; margin-bottom: 0px" class="selector" onchange="location.href='trends.php?cat='+this.value">
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
            </select>
        </div>
    </div>
    <div class="row" style="margin: 10px auto; max-width: 1400px">
        <div style="text-align: center;">
            <h2 id="graphTitle"></h2>
            <p><b>Mouse over</b> a point in the graph to see exact percentages.<br><b>Click</b> on a question in the graph's legend to hide or show that question.</p>
        </div>
        <div id="chartdiv" style="width100%; height:700px;"></div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>