<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

$ds = new DataService();
$variables = $ds->getVariables();

//Process user input
$q1 = isset($_GET['q1'])? $_GET['q1'] : 'A4';
$grp = isset($_GET['grp'])? $_GET['grp'] : 'none';
$grade = isset($_GET['grade']) ? $ds->connection->real_escape_string($_GET['grade']) : null;
$gender = isset($_GET['gender']) ? $ds->connection->real_escape_string($_GET['gender']) : null;
$race = isset($_GET['race']) ? $ds->connection->real_escape_string($_GET['race']) : null;

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
$graphHeight = min(1200,max(600,(count($groupLabels)+1)*count($mainVar->getLabels())*30+100));//height is (labels*(labels+spacing)*bar height + header height
$noresponse = $ds->getNoResponseCount($q1, $grp);
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
    <script>
        $(function() {
            mainCode = <?php echo json_encode($q1); ?>;
            groupCode = <?php echo json_encode($grp); ?>;
            questions = <?php echo json_encode($variables); ?>;

            mainQuestion = <?php echo json_encode($mainVar->question); ?>;
            groupQuestion = <?php echo json_encode($groupQuestion); ?>;
            mainTotals = <?php echo json_encode($mainVar->getMainTotals()); ?>;
            groupTotals = <?php echo json_encode($mainVar->getGroupTotals()); ?>;
            sumTotal = <?php echo json_encode($mainVar->getSumTotal()); ?>;

            createPercentChart(<?php echo json_encode($mainVar->getCountArray()); ?>, <?php echo json_encode($mainVar->getPercentArray()); ?>,
                <?php echo json_encode($mainVar->getLabels()); ?>, <?php echo json_encode($groupLabels); ?>,
                <?php echo json_encode($mainVar->summary); ?>,  <?php echo json_encode($groupSummary); ?>,
                false, null);

            if(groupLabels.length == 1)
                createSimpleTable($('#datatable'));
            else
                createCrosstabTable($('#datatable'));

            filterString = makeFilterString(<?php echo json_encode($grade); ?>,<?php echo json_encode($gender); ?>,<?php echo json_encode($race); ?>);
            var titleString = "<h4>"+mainQuestion+"</h4>";
            if(groupQuestion != null)
                titleString += "<i>compared to</i><h4>" + groupQuestion + "</h4>";
            if(filterString != null)
                titleString += "<i>" + filterString + "</i>";
            $("#graphTitle").html(titleString);

            createVariablesByCategory("Demographics",99);
            createVariablesByCategory("Alcohol",1);
            createVariablesByCategory("Tobacco",12);
            createVariablesByCategory("Drugs",5);
            createVariablesByCategory("Mental Health",9);
            createVariablesByCategory("School",4);
            createVariablesByCategory("Bullying",2);
            createVariablesByCategory("Sex and Relationships",3);
            createVariablesByCategory("Family",11);
            createVariablesByCategory("Community Support",10);
            createVariablesByCategory("Safety and Violence",13);
            createVariablesByCategory("Physical Activity",6);
            createVariablesByCategory("Nutrition",7);
            createVariablesByCategory("Self/Peer Perception",8);

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
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="h3 shadowdeep">1. Select a Question
                <div class="tipbutton"  data-toggle="tooltip" data-placement="top" title="Click a category below to expand the box and display all questions in that category. Select a question to display its graph."></div>
            </div>
            <div id="accordion1" class="accordion"></div>

            <div class="h3 shadowdeep">2. (Optional) Compare to Another Question
                <div class="tipbutton"  data-toggle="tooltip" data-placement="top" title="After selecting the primary question above, you may select a second question to look at subgroups. For example, select 'Alcohol>Binge Drinking' above, then select 'Demographics>Age' here to see how binge drinking varies with age."></div>
            </div>
            <div id="accordion2" class="accordion"></div>

            <div class="h3 shadowdeep">3. (Optional) Filter Results
                <div class="tipbutton"  data-toggle="tooltip" data-placement="top" title="You can focus your query on specific populations. After selecting question(s) above, choose which groups you want to include here. For example, choosing 'Mental Health>Considered suicide' and then filtering for 'Male' will show suicide data only for male students."></div>
            </div>
            <div class="bordergrey filterbox" style="margin-bottom: 20px;">
                <label for="filteryear">Year: </label>
                <select id="filteryear">
                    <option value="0">All</option>
                    <option value="2015">2015</option>
                </select><br>
                <label for="filtergrade">Grade: </label>
                <select id="filtergrade">
                    <option value="0">All</option>
                    <option value="1">8th</option>
                    <option value="2">10th</option>
                    <option value="3">12th</option>
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
                <div id="graphTitle"></div>
            </div>

            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the number of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <div>No Reponse: <?php echo number_format($noresponse,0);?></div>
                <input type="button" onclick="tableToExcel()" value="Export to CSV">
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>