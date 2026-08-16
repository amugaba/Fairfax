<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/TrendGroups.php';

//Get the YEAR and then instantiate the data service
$year = getInput('year') ? intval(getInput('year')) : getCurrentYear();
$dataset = getInput('ds') ?? DataService::EIGHT_TO_TWELVE;
$group = getInput('grp');
$pyramid = getInput('pyr');
//if($pyramid > 0 && $grp > 3)
//    $grp = null;

$graph = null;

if(getInput('question') != null) {
    $graph = Graph::createThreeToSucceedGraph($year, $dataset, getInput('question'), getInput('grp'), getInput('pyr'));
}

//Get variables and categories
$ds = DataService::getInstance($year, $dataset);
$cat = getInput('cat');
$trendGroup = getInput('group');
$variables = $ds->getTrendVariablesByYear();
$categories = $ds->getTrendCategoriesByYear($year);

//For 2025 forwards, exclude variables in Family, Community, and School Assets (22-24) and Disabilities (25)
//Individual assets (24) are allowed for some reason
if($year >= 2025) {
    $forbidden_categories = [21,22,23,25];
    $variables = array_filter($variables, function($var) use ($forbidden_categories) {
        return !in_array($var->category, $forbidden_categories);
    });
    $variables = array_values($variables); //Convert back to an indexed array
    $categories = array_filter($categories, function($cat) use ($forbidden_categories) {
        return !in_array($cat->code, $forbidden_categories);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Three to Succeed - Fairfax County Youth Survey</title>
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
            &nbsp;Year:
            <select id="yearSelect" style="height: 28px; font-size: 18px; padding-top: 1px; margin-left: 5px" class="selector" onchange="changeDataset()" title="Change year drop down">
                <?php foreach (getAllYearsReversed() as $yearOption) {
                    echo "<option>$yearOption</option>";
                }?>
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
                <?php if($year >= 2021 && $dataset == DataService::EIGHT_TO_TWELVE) { ?><option value="I3A">Transgender Status</option><?php } ?>
                <?php if($year >= 2023) { ?><option value="disability_cat">Disability</option><?php } ?>
            </select><br>
            <div style="text-align: center;">
                <input type="button" value="Generate Graph" class="btn" onclick="searchData()">
                <input type="button" value="Reset" class="btn" onclick="location.href = 'three-to-succeed.php'">
            </div>
        </div>
    </section>
    <main class="row" style="margin: 10px auto; max-width: 1400px">
        <?php
        if($graph == null) {
            include "three-to-succeed-instructions.php";
        }
        else { ?>
            <div style="text-align: center;">
                <div id="graphTitle"></div>
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

            <div style="text-align: center">
                <div class="grid assetsBox">
                    <?php if($year <= 2024) { ?>
                    <div class="grid-half">
                        <ul>
                            <li>Can Ask Parents for Help with Personal Problems</li>
                            <li>Performs Community Service Once a Month or More</li>
                            <li>Feels It Is Important to Accept Responsibility for Actions</li>
                        </ul>
                    </div>
                    <div class="grid-half">
                        <ul>
                            <li>Does Extracurricular Activities Once a Month or More</li>
                            <li>Teachers Recognize Good Work</li>
                            <li>Could Talk to Adults in Community about Something Important</li>
                        </ul>
                    </div>
                    <?php } else { ?>
                        <div class="grid-half">
                            <ul>
                                <li>Having adults in the community to talk to</li>
                                <li>Adults in the community let me know I am doing a good job</li>
                                <li>Parents or other adults in the family are available for help</li>
                            </ul>
                        </div>
                        <div class="grid-half">
                            <ul>
                                <li>Teachers let me know I am doing a good job</li>
                                <li>Having a trusted adult at school to ask for help</li>
                                <li>Parents or other adults in the family ask for input on most family decisions</li>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the percentage of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <?php if($graph->groupingVariable?->code == 'gender_nb') { ?>
                    <p style="font-style: italic">Due to changes in the Gender categories for 2022, direct comparison with previous years’ data is not recommended.</p>
                <?php }
                if($graph->groupingVariable?->code > 0) { ?>
                    <p style="font-style: italic">The <b>Total</b> here only includes students that answered the <b>Group Data By</b> question.<br>
                        To see the total for all students, set Group Data By to None.</p>
                <?php } ?>
                <?php $vehicleCodes = ['A5','S3','S4'];
                if(in_array($graph->mainVariable->code, $vehicleCodes)) { ?>
                    <p style="font-style: italic">*For Vehicle Safety questions, only 12th-grade students were asked.</p>
                <?php } ?>
                <?php if($graph->hasSuppression) { ?>
                    <p style="font-style: italic">Some values have been marked <b>N/A</b> due to small sample size. It is difficult to meaningfully interpret data with a sample size that is too small.</p>
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
        mainVariable: { code:null, question:null, summary:null, cutoff_summary: null, labels:null, counts:null, totals:null, assetTotals:null },
        groupingVariable: {},
        percentData: null, noResponse: null, sumTotal: null, sumPositives: null,
        ageFilter: null, sexFilter: null, raceFilter: null, incomeFilter: null,
        trendName: null, trendGroup: null, assetLabels: null
    }
    let filterString;

    //import data from php
    graph = <?= json_encode($graph); ?>;
    let questions = <?= json_encode($variables); ?>;
    let year = <?php echo json_encode($year); ?>;
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
            if(graph.labels.length === 0) {
                $(".hideIfNoGraph").hide();
                $(".showIfNoData").show();
            }
            else {
                createLineChart(graph.percentData, graph.labels, 'Number of Assets');
                createGraphTitle();
                simpleTrendTable($('#datatable'), graph.labels, graph.assetLabels, graph.percentData, "Number of Assets", graph.assetTotals);
            }
        }
    });

    //Persist user inputs in search form
    function persistInputs() {
        $('#yearSelect').val(year);
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
        let titleString = "<h4>\"" + graph.mainVariable.cutoff_summary +"\" by Number of Assets</h4>";
        if(graph.groupingVariable) {
            titleString += "<i>grouped by</i>";
            titleString += "<h4>"+graph.groupingVariable.summary+"</h4>";
        }
        $("#graphTitle").html(titleString);
    }

    function exportCSV() {
        let title = "Question: " + graph.mainVariable.cutoff_summary;
        simpleTrendCSV(title, graph.labels, graph.assetLabels, graph.percentData, graph.year, dataset, graph.groupingVariable?.summary, "Number of Assets", pyramid, graph.assetTotals);
    }

    function exportGraph() {
        exportToPDF(chart, graph.mainVariable.cutoff_summary, graph.groupingVariable?.summary, graph.year, dataset, "", pyramid);
    }

    function searchData() {
        let group = $("#groupSelect").val();
        let category = $("#category").val();
        let question = $("#question").val();

        if(question === '')
            return;//if blank, do nothing

        let url = 'three-to-succeed.php?ds=' + dataset + "&year=" + year + "&question=" + question;

        if(category !== '')
            url += '&cat='+category;
        if(group !== '')
            url += '&grp='+group;

        window.location.href = url;
    }

    function changeDataset() {
        window.location.href = "three-to-succeed.php?ds="+$("#datasetSelect").val() +"&year="+$("#yearSelect").val();
    }
</script>
</body>
</html>