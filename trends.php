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
    '2' => 'I3',
    '3' => 'race',
    '4' => 'race_eth',
    '5' => 'X9',
    '6' => 'I3A',
    '7' => 'disability_cat',
    default => null
};

if(isset($_GET['ds']) && $_GET['ds'] == '6th')
    $dataset = DataService::SIXTH;
else
    $dataset = DataService::EIGHT_TO_TWELVE;

$ds = DataService::getInstance(getCurrentYear(), $dataset);
$variables = $ds->getTrendVariables();

$showIntro = $questionCode == null;

if(!$showIntro)
{
    $variable = $ds->getCutoffVariable($questionCode);
    $groupVar = $ds->getMultiVariable($groupCode);
    $graphName = $variable->summary;
    $filter = $ds->createFilterString(null, null, null, null, $pyramid, null);

    //Get data for each year
    $years = getAllYears(); //from config.php
    $availableYears = [];
    $percentData = [];
    $yearlyTotals = [];
    foreach ($years as $year) {
        $ds = DataService::getInstance($year, $dataset);
        $yearData = ["answer" => $year];
        $availableYears[] = $year;
        if(!$ds->isVariableInData($variable->code) || !$ds->isVariableInData($groupVar->code)) //this is so slow
            $yearData['v0'] = null; //skip years where variable not in dataset
        else {
            $variable->initializeCounts($groupVar);
            $ds->getCutoffPositives($variable, $groupVar, $filter);
            $ds->getCutoffTotal($variable, $groupVar, $filter);
            for ($i = 0; $i < count($variable->counts); $i++)
                $yearData['v'.$i] = round($variable->getPercent($i+1) * 100, 1);
        }
        $percentData[] = $yearData;
        $yearlyTotals[] = count($variable->totals) === 0 ? null : array_sum($variable->totals);
    }

    //get labels and counts for data table
    $labels = $groupVar ? $groupVar->getLabels() : [$variable->summary];
    $trendNotes = getQuestionNote($variable->code, $dataset); //from TrendGroups.php
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
            var questionCode = <?php echo json_encode($questionCode); ?>;
            var group = <?php echo json_encode($grp); ?>;
            var category = <?php echo json_encode($category); ?>;
            pyramid = <?php echo json_encode($pyramid); ?>;
            dataset = <?php echo json_encode($dataset); ?>;

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

            <?php if(!$showIntro): ?>
            mainTitle = <?php echo json_encode($graphName); ?>;
            labels = <?php echo json_encode($labels); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            years = <?php echo json_encode($availableYears); ?>;
            yearlyTotals = <?php echo json_encode($yearlyTotals); ?>;

            if(years.length === 1) {
                $(".hideIfNoGraph").hide();
                $(".showIfOneYearData").show();
            }
            else if(labels.length === 0) {
                $(".hideIfNoGraph").hide();
                $(".showIfNoData").show();
            }
            else {
                chart = createLineChart(percentData, labels);
            }

            let titleString = "<h4>"+mainTitle+"</h4>";
            $("#graphTitle").html(titleString);

            simpleTrendTable($('#datatable'), labels, years, percentData, "Years", yearlyTotals);
            <?php endif; ?>

            $('[data-toggle="tooltip"]').tooltip();
        });
        function exportCSV() {
            let title = "Trends: "+mainTitle;
            simpleTrendCSV(title, labels, years, percentData, years[0]+' to '+years[years.length-1], dataset, "", "Years", pyramid, yearlyTotals);
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, null, years[0]+' to '+years[years.length-1], dataset, "", pyramid);
        }
        function searchData() {
            let group = $("#groupSelect").val();
            let category = $("#category").val();
            let question = $("#question").val();

            if(question === '')
                return;//if blank, do nothing

            let url = 'trends.php?ds=' + dataset + "&pyr=" + pyramid + "&question=" + question;

            if(category !== '')
                url += '&cat='+category;
            if(group !== '')
                url += '&grp='+group;

            window.location.href = url;
        }
        function changeDataset() {
            window.location.href = "trends.php?ds="+$("#datasetSelect").val()+"&pyr="+$("#pyramidSelect").val();
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row title">
        <div class="dataset-controls shadow" style="font-size: 22px; margin-top: 15px; color: white; text-align: center">
            Dataset:
            <select id="datasetSelect" style="width:150px; height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset(this.value)" title="Change dataset drop down">
                <option value="8to12">8th-12th grade</option>
                <option value="6th">6th grade</option>
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
                <option value="6" class="hide6">Transgender Status</option>
                <option value="7">Disability</option>
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
                <div class="showIfOneYearData" style="font-size: 1.3em; margin-top: 20px; display: none">
                    This variable was added in <?= getCurrentYear()?>. Trends will not be available until the <?= getCurrentYear()+1?> survey results are published.
                </div>
                <div class="showIfNoData" style="font-size: 1.3em; margin-top: 20px; display: none">
                    Trends are not available for this item currently.
                </div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right" class="hideIfNoGraph">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100; margin-right: 80px">
            </div>

            <div id="chartdiv" style="width100%; height:700px;"></div>

        <?php if($trendNotes != null) {
            echo "<div style='text-align: center'>
                    <p><b>**Note:</b> $trendNotes</p>
                  </div>";
        }
        ?>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the percentage of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <?php if($groupCode == 'I3') { ?>
                    <p style="font-style: italic">*For Gender, the Non-Binary and Other categories will not be reported here to preserve respondents’ privacy and anonymity.<br>
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