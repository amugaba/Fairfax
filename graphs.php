<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

//Get query inputs
$year = getInput('year') ? intval(getInput('year')) : getCurrentYear();
$dataset = getInput('ds') ?? DataService::EIGHT_TO_TWELVE;
$pyramid = getInput('pyr');
$cat1 = getInput('cat1');
$cat2 = getInput('cat2');

$graph = null; //If graph is null (no query run), show the instructions page

if(getInput('q1') != null) {
    $graph = Graph::createExploreGraph($year, $dataset, getInput('q1'), getInput('grp'), getInput('grade'), getInput('gender'), getInput('race'),
            getInput('so'), getInput('pyramid'), getInput('rsim'), getInput('trans'), getInput('disab'));
}

//Get variables and categories for dropdowns
$ds = DataService::getInstance($year, $dataset);
$variables = $ds->getVariables();
$categories = $ds->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore the Data - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <section class="row title" aria-label="Control Panel">
        <div class="dataset-controls shadow" style="font-size: 22px; margin-top: 15px; color: white; text-align: center">
            Dataset:
            <select id="datasetSelect" class="selector" onchange="changeDataset()" title="Change dataset drop down">
                <option value="8to12">8th-12th grade</option>
                <option value="6th">6th grade</option>
            </select>
            &nbsp;Year:
            <select id="yearSelect" class="selector" onchange="changeDataset()" title="Change year drop down">
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
        <form id="searchForm" class="searchbar">
            <label class="shadow" for="question1">1. Select primary question:</label>
            <select id="category1" style="width:160px" class="selector" title="Select category to filter primary question">
                <option value="" selected="selected">All categories</option>
                <?php foreach ($categories as $category) {
                    echo "<option value='$category->code'>$category->name</option>";
                }?>
            </select>
            <select id="question1" class="searchbox" required>
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" for="question2">2. (Optional) Separate data &nbsp; &nbsp; &nbsp; by another question:</label>
            <select id="category2" style="width:160px" class="selector" title="Select category to filter secondary question">
                <option value="" selected="selected">All categories</option>
                <?php foreach ($categories as $category) {
                    echo "<option value='$category->code'>$category->name</option>";
                }?>
            </select>
            <select id="question2" class="searchbox">
                <option value="" selected="selected">Select a question</option>
            </select><br>
            <label class="shadow" style="margin: 10px 0 0">3. (Optional) Filter data by:</label>
            <select id="filterGrade" class="filter selector hide6" title="Grade">
                <option value="">Grade</option>
                <option value="1">8th</option>
                <option value="2">10th</option>
                <option value="3">12th</option>
            </select>
            <select id="filterGender" class="filter selector" title="Gender">
                <option value="">Gender</option>
                <option value="1">Female</option>
                <option value="2">Male</option>
                <?php if($year >= 2022) { ?>
                    <option value="3">Non-binary</option>
                <?php } ?>
            </select>
            <select id="filterRace" class="filter selector" title="Race/Ethnicity">
                <option value="">Race/Ethnicity</option>
                <option value="1">White</option>
                <option value="2">Black</option>
                <option value="3">Hispanic</option>
                <option value="4">Asian/Pacific Islander</option>
                <option value="5">Other/Multiple</option>
            </select>
            <select id="filterRaceSimple" class="filter selector" title="Race" style="display: none">
                <option value="">Race</option>
                <option value="1">White</option>
                <option value="2">Non-white</option>
            </select>
            <select id="filterSexOrientation" class="filter selector hide6" title="Sexual Orientation">
                <option value="">Sexual Orientation</option>
                <option value="1">Heterosexual</option>
                <option value="2">Gay or lesbian</option>
                <option value="3">Bisexual</option>
                <option value="4">Not sure</option>
            </select><br class="hide6">
            <?php if($year >= 2021) { ?>
            <select id="filterTransgender" class="filter selector hide6" title="Transgender Status" style="margin: 5px 0 0 224px;">
                <option value="">Transgender Status</option>
                <option value="1">Not transgender</option>
                <option value="2">Transgender</option>
                <option value="3">Not sure</option>
            </select>
            <?php } ?>
            <?php if($year >= 2023) { ?>
            <select id="filterDisability" class="filter selector" title="Disability">
                <option value="">Disability</option>
                <option value="1">No disability</option>
                <option value="2">One or more disability</option>
                <option value="3">Not sure</option>
            </select>
            <?php } ?>
            <div style="text-align: center; margin-top: 20px">
                <input type="submit" value="Generate Graph" class="btn">
                <input type="button" value="Reset" class="btn" onclick="location.href = 'graphs.php'">
            </div>
        </form>
    </section>
    <main class="row" style="margin: 10px auto; max-width: 1400px">
        <?php
        if($graph == null) {
            include "instructions.php";
        }
        else if($graph->mainVarUnavailable || $graph->groupingVarUnavailable)
        { ?>
            <div style="text-align: center; font-size: 18px">
                <p>The variable you selected was not collected during the year you selected.<br>Please choose a different year or different variable.</p>
            </div>
        <?php }
        else if($graph->belowThreshold)
        { ?>
            <div style="font-size: 18px; width: 800px; margin: 0 auto">
                <p>The graph and table cannot be displayed because the query contains one or more sensitive variables and/or too many filters,
                    making the sample size too small. We do this to protect the privacy and anonymity of our respondents.</p>
                <p>Additionally, it is difficult to meaningfully interpret data with a sample size that is too small. Consider choosing different
                    variables or removing some of the demographic filters to increase the sample size.</p>
            </div>
        <?php }
        else //Display the graph and table
        { ?>
            <div style="text-align: center;">
                <div id="graphTitle"></div>
                <?php if($graph->notes != null) {
                    echo "<p><b>Note:</b> $graph->notes</p>";
                } ?>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100">
            </div>

            <div id="chartdiv" style="width: 100%; height:<?php echo $graph->graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="display: inline">Data Table</h3>
                <div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the number of students in each category. To save this data, click Export to CSV."></div>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>

                <?php $vehicleCodes = ['A5','S3','S4'];
                if(in_array($graph->mainVariable->code, $vehicleCodes) || in_array($graph->groupingVariable?->code, $vehicleCodes)) { ?>
                    <p style="font-style: italic">*For Vehicle Safety questions, only 12th-grade students were asked.</p>
                <?php } ?>

                <div>No Response: <?php echo number_format($graph->noResponse);?></div>
                <input type="button" onclick="exportCSV()" value="Export to CSV" class="btn btn-blue" style="margin-top: 10px">
            </div>
        <?php } ?>
    </main>
</div>
<?php include_footer();
include_js(); ?>
<script>
    let graph = {
        year: null, dataset: null, pyramidFilter: null, belowThreshold: null, mainVarUnavailable: null, groupVarUnavailable: null,
        mainVariable: { code:null, question:null, summary:null, labels:null, counts:null, totals:null },
        groupingVariable: {},
        percentData: null, noResponse: null, sumTotal: null, sumPositives: null,
        gradeFilter: null, genderFilter: null, raceFilter: null, raceSimplifiedFilter: null, sexOrientationFilter: null, transgenderFilter: null, disabilityFilter: null
    }
    let filterString;

    //import data from php
    graph = <?= json_encode($graph); ?>;
    let questions = <?= json_encode($variables); ?>;
    let year = <?= json_encode($year); ?>; //These are set even when graph is null
    let dataset = <?= json_encode($dataset); ?>;
    let pyramid = <?= json_encode($pyramid); ?>;
    let cat1 = <?= json_encode($cat1); ?>;
    let cat2 = <?= json_encode($cat2); ?>;

    if(graph != null && !graph.belowThreshold && !graph.mainVarUnavailable && !graph.groupVarUnavailable) {
        createBarGraph(graph.percentData, graph.mainVariable.question, graph.groupingVariable?.question,
            graph.groupingVariable?.labels || ['Total'], null, graph.mainVariable.summary);
    }

    $(function() {
        //Enable jQuery elements
        enableSelect2(questions, "#category1", "#question1");
        enableSelect2(questions, "#category2", "#question2", true);
        $('[data-toggle="tooltip"]').tooltip();
        $("#searchForm").on( "submit", searchData);

        showHideFields();
        persistInputs();

        if(graph != null && !graph.belowThreshold && !graph.mainVarUnavailable && !graph.groupVarUnavailable)
        {
            if(graph.groupingVariable == null)
                createSimpleExplorerTable($('#datatable'), graph.mainVariable.labels, graph.mainVariable.counts, graph.sumTotal);
            else
                createCrosstabExplorerTable($('#datatable'), graph.mainVariable.summary, graph.groupingVariable.summary,
                    graph.mainVariable.labels, graph.groupingVariable.labels, graph.mainVariable.counts,
                    graph.sumPositives, graph.mainVariable.totals, graph.sumTotal);

            filterString = makeFilterString(graph.gradeFilter, graph.genderFilter, graph.raceFilter, graph.sexOrientationFilter, graph.raceSimplifiedFilter,
                graph.transgenderFilter, graph.disabilityFilter);

            createGraphTitle();
        }
    });

    //Persist user inputs in search form
    function persistInputs() {
        $('#yearSelect').val(year);
        $('#datasetSelect').val(dataset);
        $('#pyramidSelect').val(pyramid);
        $('#category1').val(cat1);
        $("#category1").trigger('change');
        $('#category2').val(cat2);
        $("#category2").trigger('change');

        if(graph != null) {
            $('#question1').val(graph.mainVariable.code);
            $("#question1").trigger('change');
            if (graph.groupingVariable != null) {
                $('#question2').val(graph.groupingVariable.code);
                $("#question2").trigger('change');
            }
            $('#filterGrade').val(graph.gradeFilter);
            $('#filterGender').val(graph.genderFilter);
            $('#filterRace').val(graph.raceFilter);
            $('#filterRaceSimple').val(graph.raceSimplifiedFilter);
            $('#filterSexOrientation').val(graph.sexOrientationFilter);
            $('#filterTransgender').val(graph.transgenderFilter);
            $('#filterDisability').val(graph.disabilityFilter);
        }
    }

    //Hide/show fields based on dataset and pyramid
    function showHideFields() {
        if(dataset === '6th') {
            $(".hide6").hide();
        }
        if(pyramid > 0) {
            $("#filterRace").hide();
            $("#filterSexOrientation").hide();
            $("#filterRaceSimple").show();
        }
    }

    //Create a string and write it to the title DIV
    function createGraphTitle() {
        let titleString = "<h4>"+graph.mainVariable.question+"</h4>";
        if(graph.groupingVariable != null)
            titleString += "<h4><i>compared to</i></h4><h4>" + graph.groupingVariable.question + "</h4>";
        if(filterString != null)
            titleString += "<h4><i>" + filterString + "</i></h4>";
        $("#graphTitle").html(titleString);
    }

    function exportCSV() {
        if(graph.groupingVariable == null)
            simpleExplorerCSV(graph.mainVariable.question, graph.mainVariable.labels, graph.mainVariable.counts, graph.mainVariable.totals, graph.year,
                graph.dataset, filterString, graph.pyramidFilter);
        else
            crosstabExplorerCSV(graph.mainVariable.question, graph.groupingVariable.question, graph.mainVariable.labels, graph.groupingVariable.labels,
                graph.mainVariable.counts, graph.sumPositives, graph.mainVariable.totals, graph.sumTotal, filterString, graph.year, graph.dataset, graph.pyramidFilter);
    }

    function exportGraph() {
        exportToPDF(chart, graph.mainVariable.question, graph.groupingVariable?.question, graph.year, graph.dataset, filterString, graph.pyramidFilter);
    }

    function searchData(e) {
        e.preventDefault();
        let q1 = $('#question1').val();
        let q2 = $('#question2').val();
        let cat1 = $('#category1').val();
        let cat2 = $('#category2').val();
        let grade = $("#filterGrade").val();
        let gender = $("#filterGender").val();
        let race = $("#filterRace").val();
        let raceSimplified = $("#filterRaceSimple").val();
        let sexOrientation = $("#filterSexOrientation").val();
        let transgender = $("#filterTransgender").val();
        let disability = $("#filterDisability").val();

        if(q1 !== '') {
            let url = 'graphs.php?ds='+$('#datasetSelect').val()+"&year="+$("#yearSelect").val()+'&q1='+q1;

            if(q2 !== '' && q2 !== undefined)
                url += '&grp='+q2;
            if(cat1 !== '' && cat1 !== undefined)
                url += '&cat1='+cat1;
            if(cat2 !== '' && cat2 !== undefined)
                url += '&cat2='+cat2;
            if(grade !== '' && grade !== undefined)
                url += "&grade="+grade;
            if(gender !== '' && gender !== undefined)
                url += "&gender="+gender;
            if(race !== '' && race !== undefined)
                url += "&race="+race;
            if(raceSimplified !== '' && raceSimplified !== undefined)
                url += "&rsim="+raceSimplified;
            if(sexOrientation !== '' && sexOrientation !== undefined)
                url += "&so="+sexOrientation;
            if(transgender !== '' && transgender !== undefined)
                url += "&trans="+transgender;
            if(disability !== '' && disability !== undefined)
                url += "&disab="+disability;

            window.location.href = url;
        }
    }

    function changeDataset() {
        window.location.href = "graphs.php?ds="+$('#datasetSelect').val()+"&year="+$("#yearSelect").val();
    }
</script>
</body>
</html>