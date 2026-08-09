<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/CategoryData.php';

//Get query inputs
$year = getInput('year') ? intval(getInput('year')) : getCurrentYear();
$dataset = getInput('ds') ?? DataService::EIGHT_TO_TWELVE;
$pyramid = getInput('pyr') ?? '';
$category = getInput('cat') ?? 1;
$group = getInput('grp');

//Get categories and variables
$highlightGroup = getHighlightGroup($category, $dataset, $year);
$categoryLinks = getHighlightLinks($year);

//Generate the graph
$graph = Graph::createHighlightsGraph($year, $dataset, $category, $group);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Highlights - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <section class="col-md-3 sidebar" aria-label="Control Panel">
            <div class="dataset-controls">
                <p class="shadowdeep" style="font-size: 18px; margin-top: 15px;">Showing highlights for</p>
                <div>
                    <label for="yearSelect" class="categories">Year:</label>
                    <select id="yearSelect" class="selector" onchange="changeYear(this.value)" title="Change year drop down">
                        <?php foreach (getAllYearsReversed() as $yearOption) {
                            echo "<option>$yearOption</option>";
                        }?>
                    </select>
                </div>
                <div>
                    <label for="datasetSelect">Dataset:</label>
                    <select id="datasetSelect" class="selector" onchange="changeDataset(this.value)" title="Change dataset drop down">
                        <option value="8to12">8th-12th grade</option>
                        <option value="6th">6th grade</option>
                    </select>
                </div>
                <!--<div>
                    <label for="pyramidSelect">Pyramid:</label>
                    <select id="pyramidSelect" class="selector" onchange="changePyramid(this.value)" title="Change pyramid drop down">
                        <option value="">All</option>
                        <?php for($i=1; $i<=25; $i++) {
                            echo "<option value='$i'>$i</option>";
                        } ?>
                    </select>
                    <div class="tipbutton" style="margin-left:5px; position: absolute" data-toggle="tooltip" data-placement="top"
                         title="When a pyramid is selected, data can only be grouped by grade, gender, and race (simplified) to preserve anonymity."></div>
                </div>-->
            </div>
            <h2 class="shadowdeep">Select a Category
                <div class="tipbutton"  data-toggle="tooltip" data-placement="top" title="Each category highlights several significant behaviors and shows the percentage of students that engaged in those behaviors."></div>
            </h2>
                <ul class="categories shadow">
                    <?= $categoryLinks ?>
                </ul>
        </section>
        <main class="col-md-9 mainbar text-center">
            <div style="text-align: center;">
                <h2 id="graphTitle"></h2>
                <div id="explanation" style="max-width:1200px; margin: 0 auto"><?php echo $highlightGroup->explanation;?></div>
                <p class="hideIfNoGraph"><b>Mouse over</b> the graph's labels and bars to see in more detail what each element represents.</p>
                <div class="showIfNoGraph" style="font-size: 1.3em; margin-top: 20px; display: none">
                    The survey did not ask about this topic in <?php echo $year ?>. Please select a different year, grade level, or topic.
                </div>
            </div>

            <div class="groupbox hideIfNoGraph" style="width: max-content; margin: 20px auto 0; padding-right: 20px">
                <p style="font-weight: bold; margin-right: 10px; display: inline">Group data by:</p>
                <div id="grouping">
                    <input id="none" name="grouping" type="radio" value="" checked="checked"/><label for="none">None</label>
                    <input id="gradeButton" name="grouping" class="hide6" type="radio" value="I2"/><label for="gradeButton">Grade</label>
                    <?php if($year >= 2022) { ?>
                        <input id="gender" name="grouping" type="radio" value="gender_nb"/><label for="gender">Gender</label>
                    <?php } else { ?>
                        <input id="gender" name="grouping" type="radio" value="I3"/><label for="gender">Gender</label>
                    <?php } ?>
                    <?php if($pyramid == ''): ?><input id="race" name="grouping" type="radio" value="race_eth"/><label for="race">Race/Ethnicity</label>
                    <?php else: ?><input id="raceSimple" name="grouping" type="radio" value="race"/><label for="raceSimple">Race (simplified)</label><?php endif; ?>
                    <?php if($year >= 2021 && $dataset == "8to12"){ ?><input id="transgender" name="grouping" type="radio" value="I3A"/><label for="transgender">Transgender Status</label><?php } ?>
                    <?php if($year >= 2023){ ?><input id="disability" name="grouping" type="radio" value="disability_cat"/><label for="disability">Disability</label><?php } ?>
                </div>
                <div id="groupTooltip" class="tipbutton" style="margin:0 0 3px 17px"  data-toggle="tooltip" data-placement="top" title="You can separate students by grade, gender, race/ethnicity, transgender status, or disability to see how each group answered."></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right" class="hideIfNoGraph">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100">
            </div>
            <div id="chartdiv" style="width: 100%; height:<?php echo $graph->graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the number of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <?php if($group == 'gender_nb') { ?>
                    <p style="font-style: italic">Due to changes in the Gender categories for 2022, direct comparisons with previous years’ data is not recommended.</p>
                <?php }
                if($group > 0) { ?>
                    <p style="font-style: italic">The <b>Total</b> here only includes students that answered the <b>Group Data By</b> question.<br>
                        To see the total for all students, set Group Data By to None.</p>
                <?php } ?>
                <?php if($category == 5) { ?>
                    <p style="font-style: italic">*For Vehicle Safety questions, only 12th-grade students were asked.</p>
                <?php } ?>
                <input type="button" onclick="exportCSV()" class="btn btn-blue" value="Export to CSV" style="margin-top: 10px">
            </div>
        </main>
    </div>
</div>
<?php include_footer();
include_js(); ?>
<script>
    //Inputs, used to set links
    let year = <?php echo json_encode($year); ?>;
    let category = <?php echo json_encode($category); ?>;
    let dataset = <?php echo json_encode($dataset); ?>;
    let group = <?php echo json_encode($group); ?>;
    let pyramid = <?php echo json_encode($pyramid); ?>;

    $(function() {
        graph = <?= json_encode($graph); ?>;
        mainTitle = <?php echo json_encode($highlightGroup->title); ?>;

        //hide some inputs based on dataset or category
        if(dataset === '6th')
            $(".hide6").hide();
        if(category === 5)
            $("#gradeButton").hide(); //vehicle safety

        SetGroupOptions(dataset, year, category);

        if(graph.percentData.length > 0) {
            createBarGraph(graph.percentData, mainTitle, graph.groupingVariable?.summary, graph.groupingVariable?.labels || ['Total'], graph.tooltips);

            if (graph.groupingVariable == null)
                createSimpleHighlightTable($('#datatable'), graph.mainVariable.labels, graph.percentData, graph.sumTotals);
            else
                createCrosstabHighlightTable($('#datatable'), mainTitle, graph.groupingVariable.summary, graph.mainVariable.labels, graph.groupingVariable.labels, graph.percentData, graph.sumTotals);
        }
        else {
            $(".hideIfNoGraph").hide();
            $(".showIfNoGraph").show();
        }

        $("#graphTitle").html(year + " Highlights: " + mainTitle);
        $('#grouping :input[value='+group+']').prop("checked",true);
        $('#yearSelect').val(year);
        $('#datasetSelect').val(dataset);
        $('#pyramidSelect').val(pyramid);
        //$('#grouping').buttonset();
        $('#grouping').controlgroup();
        $('#grouping :input').click(function() {
            window.location = generateHighlightLink(year, dataset, category, this.value, pyramid);
        });
        $('[data-toggle="tooltip"]').tooltip();

        //set category links, preserve year and dataset, reset grouping
        $('.categories li a').each(function(){
            $(this).attr('href', generateHighlightLink(year, dataset, $(this).data('category'), '', pyramid));
        });
    });

    //change grouping box based on dataset and year
    function SetGroupOptions(dataset, year, category)
    {
        let groupOptions = [];
        if(dataset === '8to12' && category !== 5)
            groupOptions.push('grade');
        groupOptions.push('gender');
        groupOptions.push('race/ethnicity');
        if(dataset === '8to12' && year >= 2021)
            groupOptions.push('transgender status');
        if(year >= 2023 )
            groupOptions.push('disability');
        groupOptions[groupOptions.length-1] = "or " + groupOptions[groupOptions.length-1];
        let groupText = groupOptions.join(", ");
        if(groupOptions.length <= 2)
            groupText = groupOptions.join(" ");
        $("#groupTooltip").attr("title", "You can separate students by " + groupText + " to see how each group answered.");

        if(groupOptions.length >= 5) {
            let splitLabel = $("#grouping > label:nth-child(8)"); //insert linebreak after this and indent
            splitLabel.after("<br>");
        }
        //return groupOptions;
    }
    function changeYear(yr) {
        window.location = generateHighlightLink(yr, dataset, '', '', pyramid);
    }
    function changeDataset(ds) {
        window.location = generateHighlightLink(year, ds, category, '', pyramid);
    }
    function changePyramid(pyramid) {
        window.location = generateHighlightLink(year, dataset, category, '', pyramid);
    }
    function changeCategory(cat) {
        window.location = generateHighlightLink(year, dataset, cat, '', pyramid);
    }
    function exportCSV() {
        if(graph.groupingVariable == null)
            simpleHighlightCSV(mainTitle, graph.mainVariable.labels, graph.percentData, graph.sumTotals, year, dataset, pyramid);
        else
            crosstabHighlightCSV(mainTitle, graph.groupingVariable.summary, graph.mainVariable.labels, graph.groupingVariable.labels, graph.percentData, graph.sumTotals, year, dataset, pyramid);
    }
    function exportGraph() {
        exportToPDF(chart, mainTitle, graph.groupingVariable?.summary, year, dataset, null);
    }
    //create a link to highlights page based on current year, dataset, category, and group variables
    function generateHighlightLink(yr, ds, cat, grp, pyramid){
        if(ds === '6th' && grp === 'I2')
            return "highlights.php?year="+yr+"&ds="+ds+"&cat="+cat+"&pyr="+pyramid;
        else
            return "highlights.php?year="+yr+"&ds="+ds+"&cat="+cat+"&grp="+grp+"&pyr="+pyramid;
    }
</script>
</body>
</html>