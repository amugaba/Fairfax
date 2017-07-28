<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

if(isset($_GET['year']))
    $year = intval($_GET['year']);
else
    $year = getCurrentYear();

$ds = DataService::getInstance($year, DataService::EIGHT_TO_TWELVE);
$variables = $ds->getVariables();

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
}
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.full.js"></script>
    <script>
        $(function() {
            questions = <?php echo json_encode($variables); ?>;
            year = <?php echo json_encode($year); ?>;

            <?php if(!$showIntro): ?>
            mainCode = <?php echo json_encode($q1); ?>;
            groupCode = <?php echo json_encode($grp); ?>;
            mainQuestion = <?php echo json_encode($mainVar->question); ?>;
            groupQuestion = <?php echo json_encode($groupQuestion); ?>;
            mainTotals = <?php echo json_encode($mainVar->getMainTotals()); ?>;
            groupTotals = <?php echo json_encode($mainVar->getGroupTotals()); ?>;
            sumTotal = <?php echo json_encode($mainVar->getSumTotal()); ?>;

            chart = createPercentChart(<?php echo json_encode($mainVar->getCountArray()); ?>, <?php echo json_encode($mainVar->getPercentArray()); ?>,
                <?php echo json_encode($mainVar->getLabels()); ?>, <?php echo json_encode($groupLabels); ?>,
                <?php echo json_encode($mainVar->summary); ?>,  <?php echo json_encode($groupSummary); ?>,
                false, null);

            if(groupLabels.length == 1)
                createSimpleTable($('#datatable'));
            else
                createCrosstabTable($('#datatable'));

            var grade = <?php echo json_encode($grade); ?>;
            var gender = <?php echo json_encode($gender); ?>;
            var race = <?php echo json_encode($race); ?>;
            var cat1 = <?php echo json_encode($cat1); ?>;
            var cat2 = <?php echo json_encode($cat2); ?>;

            filterString = makeFilterString(grade, gender, race);
            titleString = "<h4>"+mainQuestion + " - " + year + "</h4>";
            if(groupQuestion != null)
                titleString += "<i>compared to</i><h4>" + groupQuestion + "</h4>";
            if(filterString != null)
                titleString += "<i>" + filterString + "</i>";
            $("#graphTitle").html(titleString);

            if(cat1 != null) {
                $('#category1').val(cat1);
                refreshQuestions(cat1,'#question1');
            }
            else
                refreshQuestions('','#question1');
            if(cat2 != null) {
                $('#category2').val(cat2);
                refreshQuestions(cat2,'#question2');
            }
            else
                refreshQuestions('','#question2');

            if(mainCode != null) {
                $('#question1').val(mainCode);
                $("#question1").trigger('change');
            }
            if(groupCode != null && groupCode != 'none') {
                $('#question2').val(groupCode);
                $("#question2").trigger('change');
            }
            if(year != null) {
                $('#filteryear').val(year);
            }
            if(grade != null) {
                $('#filtergrade').val(grade);
            }
            if(gender != null) {
                $('#filtergender').val(gender);
            }
            if(race != null) {
                $('#filterrace').val(race);
            }
            <?php else: ?>
            refreshQuestions('','#question1');
            refreshQuestions('','#question2');
            <?php endif; ?>

            $('[data-toggle="tooltip"]').tooltip();
        });

        function refreshQuestions(category, target) {
            $(target).val('');
            $(target).trigger('change');
            $(target).find("option:gt(0)").remove();//remove all but first option

            //construct array of questions in this category
            var data = [];
            for(var i=0; i<questions.length; i++) {
                if(questions[i].category != null && (category == "" || category == questions[i].category))
                    data.push({id:questions[i].code, text:questions[i].summary});
            }
            //add questions to dropdown
            $(target).select2({data:data,
                containerCssClass: "searchbox",
                dropdownCssClass: "searchbox",
            placeholder: "Select an option"});
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

            if(q1 != '') {
                var url = 'graphs.php?q1='+q1;

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

                window.location.href = url;
            }
        }
    </script>
    <script src="js/exportgraph.js"></script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row" style="background-color: #2e6da4;">
        <div class="searchbar">
            <label class="shadow">1. Select primary question:</label>
            <select id="category1" style="width:160px" class="selector" onchange="refreshQuestions(this.value, '#question1')">
                <option value="" selected="selected">All categories</option>
                <option value="99">Demographics</option>
                <option value="1">Alcohol</option>
                <option value="12">Tobacco</option>
                <option value="5">Drugs</option>
                <option value="2">Bullying & Cyberbullying</option>
                <option value="14">Harassment</option>
                <option value="3">Dating Aggression</option>
                <option value="13">Other Aggressive Behaviors</option>
                <option value="17">Vehicle Safety</option>
                <option value="6">Physical Activity</option>
                <option value="7">Nutrition</option>
                <option value="19">Unhealthy Weight Loss Behaviors</option>
                <option value="9">Mental Health</option>
                <option value="18">Sexual Health</option>
                <option value="4">School</option>
                <option value="11">Family</option>
                <option value="10">Community Support</option>
                <option value="16">Civic Engagement</option>
                <option value="15">Time Use</option>
                <option value="8">Self/Peer Perception</option>
            </select>
            <select id="question1" style="width:300px" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow">2. (Optional) Separate data &nbsp; &nbsp; &nbsp; by another question:</label>
            <select id="category2" style="width:160px" class="selector" onchange="refreshQuestions(this.value, '#question2')">
                <option value="" selected="selected">All categories</option>
                <option value="99">Demographics</option>
                <option value="1">Alcohol</option>
                <option value="12">Tobacco</option>
                <option value="5">Drugs</option>
                <option value="2">Bullying & Cyberbullying</option>
                <option value="14">Harassment</option>
                <option value="3">Dating Aggression</option>
                <option value="13">Other Aggressive Behaviors</option>
                <option value="17">Vehicle Safety</option>
                <option value="6">Physical Activity</option>
                <option value="7">Nutrition</option>
                <option value="19">Unhealthy Weight Loss Behaviors</option>
                <option value="9">Mental Health</option>
                <option value="18">Sexual Health</option>
                <option value="4">School</option>
                <option value="11">Family</option>
                <option value="10">Community Support</option>
                <option value="16">Civic Engagement</option>
                <option value="15">Time Use</option>
                <option value="8">Self/Peer Perception</option>
            </select>
            <select id="question2" style="width:300px" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 10px">3. Select which year to view:</label>
            <select id="filteryear" class="filter selector">
                <option value="2016">2016</option>
                <option value="2015">2015</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 20px">4. (Optional) Filter data by:</label>
            <select id="filtergrade" class="filter selector">
                <option value="">Grade</option>
                <option value="1">8th</option>
                <option value="2">10th</option>
                <option value="3">12th</option>
            </select>
            <select id="filtergender" class="filter selector">
                <option value="">Gender</option>
                <option value="1">Female</option>
                <option value="2">Male</option>
            </select>
            <select id="filterrace" class="filter selector">
                <option value="">Race</option>
                <option value="1">White</option>
                <option value="2">Black</option>
                <option value="3">Hispanic</option>
                <option value="4">Asian/Pacific Islander</option>
                <option value="5">Other/Multiple</option>
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
        else: ?>
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
                <input type="button" onclick="tableToExcel()" value="Export to CSV" class="btn btn-blue" style="margin-top: 10px">
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>