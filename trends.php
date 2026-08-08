<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/TrendGroups.php';

//Get the YEAR and then instantiate the data service
$yearRange = getInput('year') ?? Trend::PRE_2024;
$dataset = getInput('ds') ?? DataService::EIGHT_TO_TWELVE;
$group = getInput('grp');
$pyramid = getInput('pyr');
//if($pyramid > 0 && $grp > 3)
//    $grp = null;

$graph = null;

if(getInput('question') != null) {
    $graph = Graph::createTrendsGraph($yearRange, $dataset, getInput('question'), getInput('grp'), getInput('pyr'));
}

//Get variables and categories
$ds = DataService::getInstance(getCurrentYear(), $dataset);
$cat = getInput('cat');
$variables = $ds->getTrendVariablesByYearRange($yearRange);
$latestYear = ($yearRange == Trend::PRE_2024) ? 2024 : getCurrentYear();
$categories = $ds->getTrendCategoriesByYear($latestYear);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trends - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <section class="row title" aria-label="Control Panel">
        <div class="dataset-controls shadow" style="font-size: 22px; margin-top: 15px; color: white; text-align: center">
            Dataset:
            <select id="datasetSelect" style="width:150px; height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset()" title="Change dataset drop down">
                <option value="8to12">8th-12th grade</option>
                <option value="6th">6th grade</option>
            </select>
            &nbsp;Years:
            <select id="yearSelect" class="selector" onchange="changeDataset()" title="Change year drop down">
                <option value="<?= Trend::PRE_2024 ?>">2024 and earlier</option>
                <option value="<?= Trend::POST_2025 ?>">2025 and beyond</option>
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
        <div class="searchbar">
            <label class="shadow" for="question">1. Select a question:</label>
            <select id="category" style="width:160px" class="selector" title="Select category to filter primary question">
                <option value="" selected="selected">All categories</option>
                <?php foreach ($categories as $category) {
                    echo "<option value='$category->code'>$category->name</option>";
                }?>
            </select>
            <select id="question" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 20px">2. (Optional) Group data by:</label>
            <select id="groupSelect" class="filter selector" title="Group data by">
                <option value="">None</option>
                <option value="I2" class="hide6">Grade</option>
                <option value="gender_nb">Gender</option>
                <option value="race" class="isPyramid">Race (simplified)</option>
                <option value="race_eth" class="notPyramid">Race/Ethnicity</option>
                <option value="X9" class="notPyramid hide6">Sexual Orientation</option>
                <option value="I3A" class="hide6">Transgender Status</option>
                <option value="disability_cat">Disability</option>
            </select><br>
            <div style="text-align: center;">
                <input type="button" value="Generate Graph" class="btn" onclick="searchData()">
                <input type="button" value="Reset" class="btn" onclick="location.href = 'trends.php'">
            </div>
        </div>
    </section>
    <main class="row" style="margin: 10px auto; max-width: 1400px">
        <?php
        if($graph == null) {
            include "trends-instructions.php";
        }
        else { ?>
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

            <div id="chartdiv" style="width: 100%; height:700px;"></div>

            <?php if($graph->notes != null) {
                echo "<div style='text-align: center'>
                        <p><b>**Note:</b> $graph->notes</p>
                      </div>";
            } ?>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the percentage of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <?php if($graph->groupingVariable?->code == 'gender_nb') { ?>
                    <p style="font-style: italic">Due to changes in the Gender categories for 2022, direct comparisons with previous years’ data is not recommended.</p>
                <?php }
                if($graph->groupingVariable?->code) { ?>
                    <p style="font-style: italic">The <b>Total</b> here only includes students that answered the <b>Group Data By</b> question.<br>
                        To see the total for all students, set Group Data By to None.</p>
                <?php }
                $vehicleCodes = ['A5','S3','S4'];
                if(in_array($graph->mainVariable->code, $vehicleCodes)) { ?>
                    <p style="font-style: italic">*For Vehicle Safety questions, only 12th-grade students were asked.</p>
                <?php } ?>
                <input type="button" onclick="exportCSV()" value="Export to CSV" class="btn btn-blue" style="margin-top: 10px">
            </div>
        <?php } ?>
    </main>
</div>
<?php include_footer();
include_js(); ?>
<script>
    let graph = {
        mainVariable: { code:null, question:null, summary:null, cutoff_summary: null, labels:null, counts:null, totals:null, yearlyTotals:null },
        groupingVariable: {},
        percentData: null, noResponse: null, sumTotal: null, sumPositives: null,
        ageFilter: null, sexFilter: null, raceFilter: null, incomeFilter: null,
        trendName: null, trendGroup: null, yearsInGraph: null
    }
    let filterString, year;

    //import data from php
    graph = <?= json_encode($graph); ?>;
    let questions = <?= json_encode($variables); ?>;
    let yearRange = <?php echo json_encode($yearRange); ?>;
    let pyramid = <?php echo json_encode($pyramid); ?>;
    let dataset = <?php echo json_encode($dataset); ?>;
    let category = <?= json_encode($cat); ?>;
    let group = <?= json_encode($group); ?>;

    $(function() {
        //Enable jQuery elements
        enableSelect2(questions, "#category", "#question");
        $('[data-toggle="tooltip"]').tooltip();
        $("#searchForm").on( "submit", searchData);

        showHideFields();
        persistInputs();

        if(graph != null) {
            if(graph.yearsInGraph.length === 1) {
                $(".hideIfNoGraph").hide();
                $(".showIfOneYearData").show();
            }
            else if(graph.labels.length === 0) {
                $(".hideIfNoGraph").hide();
                $(".showIfNoData").show();
            }
            else {
                createLineChart(graph.percentData, graph.labels);
                createGraphTitle();
                simpleTrendTable($('#datatable'), graph.labels, graph.yearsInGraph, graph.percentData, "Years", graph.yearlyTotals);
            }
        }
    });

    //Persist user inputs in search form
    function persistInputs() {
        $('#yearSelect').val(yearRange);
        $('#datasetSelect').val(dataset);
        $('#pyramidSelect').val(pyramid);
        $('#groupSelect').val(group);
        let categorySelect = $("#category");
        categorySelect.val(category);
        categorySelect.trigger('change');

        if(graph != null) {
            let questionSelect = $("#question");
            questionSelect.val(graph.mainVariable.code);
            questionSelect.trigger('change');
        }
    }

    //Hide/show fields based on dataset and pyramid
    function showHideFields() {
        if(dataset === '6th')
            $(".hide6").hide();
        if(pyramid > 0)
            $(".notPyramid").hide();
        else
            $(".isPyramid").hide();
    }

    //Create a string and write it to the title DIV
    function createGraphTitle() {
        let titleString = "<h4>" + graph.mainVariable.cutoff_summary +"</h4>";
        if(graph.groupingVariable) {
            titleString += "<i>grouped by</i>";
            titleString += "<h4>"+graph.groupingVariable.summary+"</h4>";
        }
        $("#graphTitle").html(titleString);
    }

    function exportCSV() {
        let title = "Trends: " + graph.mainVariable.cutoff_summary;
        simpleTrendCSV(title, graph.labels, graph.yearsInGraph, graph.percentData, graph.yearsInGraph[0]+' to '+graph.yearsInGraph[graph.yearsInGraph.length-1],
            dataset, graph.groupingVariable?.summary, "Years", pyramid, graph.yearlyTotals);
    }

    function exportGraph() {
        exportToPDF(chart, graph.mainVariable.cutoff_summary, graph.groupingVariable?.summary, graph.yearsInGraph[0]+' to '+graph.yearsInGraph[graph.yearsInGraph.length-1],
            dataset, "", pyramid);
    }

    function searchData() {
        let group = $("#groupSelect").val();
        let category = $("#category").val();
        let question = $("#question").val();

        if(question === '')
            return;//if blank, do nothing

        let url = 'trends.php?ds=' + dataset + "&year=" + yearRange + "&question=" + question;

        if(category !== '')
            url += '&cat='+category;
        if(group !== '')
            url += '&grp='+group;

        window.location.href = url;
    }

    function changeDataset() {
        window.location.href = "trends.php?ds="+$("#datasetSelect").val() +"&year="+$("#yearSelect").val();
    }
</script>
</body>
</html>