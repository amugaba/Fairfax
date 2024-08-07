<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';
require_once 'hidden/CategoryData.php';

if(isset($_GET['year']))
    $year = intval($_GET['year']);
else
    $year = getCurrentYear();

if(isset($_GET['ds']) && $_GET['ds'] == '6th')
    $dataset = DataService::SIXTH;
else
    $dataset = DataService::EIGHT_TO_TWELVE;

$ds = DataService::getInstance($year, $dataset);
$cat = $_GET['cat'] ?? 1;
$grp = $_GET['grp'] ?? '';
$pyramid = ''; //$_GET['pyr'] ?? ''; Uncomment to re-enable

$highlightGroup = getHighlightGroup($cat, $dataset, $year);
$groupVar = $ds->getMultiVariable($grp);
if($groupVar != null)
    $groupVar->labels[] = "Total";
$variablesInGraph = [];
$filter = $ds->createFilterString(null,null,null,null,$pyramid);

//get data for each question
for($i = 0; $i < count($highlightGroup->codes); $i++)
{
    if(!$ds->isVariableInData($highlightGroup->codes[$i]))
        continue;
    $variable = $ds->getCutoffVariable($highlightGroup->codes[$i]);
    $variable->initializeCounts($groupVar);
    $variable->summary = $highlightGroup->labels[$i];
    $ds->getCutoffPositivesWithRollup($variable, $groupVar, $filter);
    $ds->getCutoffTotalWithRollup($variable, $groupVar, $filter);
    $variable->calculatePercents();
    $variablesInGraph[] = $variable;
}

//Create the data structure used by AmCharts for bar graphs
//[['answer' => Var1 label, 'v0' => Group0 percent, 'v1' => Group1 percent, ...], ['answer' => Var 2 label, ...]]
$percentData = [];
foreach ($variablesInGraph as $variable) {
    $percentArray['answer'] = $variable->summary;
    for($i=0; $i<count($variable->counts); $i++) {
        $percentArray['v'.$i] = $variable->percents[$i];
    }
    $percentData[] = $percentArray;
}

//Also create data for display in graph and table
$mainLabels = []; //labels for main variable
$counts = []; //[[var1 counts], [var2 counts], ...] where [var1 counts] = [group1, group2, ...]
$variableTotals = []; //sum valid cases for a variable
$tooltips = []; //mouse-over pop-ups to explain graph labels and bars

foreach ($variablesInGraph as $variable) {
    $mainLabels[] = $variable->summary;
    $counts[] = $variable->counts;
    if($groupVar !== null)
        $variableTotals[] = end($variable->totals); //last element is the total
    else
        $variableTotals[] = array_sum($variable->totals);
    $tooltips[] = $variable->tooltip;
}

//Group variable data in case it's null
if($groupVar != null){
    $groupLabels = $groupVar->getLabels();
    $groupSummary = $groupVar->summary;
    $groupTitle = $groupVar->question;
}
else {
    $groupLabels = ['Total'];
    $groupSummary = null;
    $groupTitle = null;
}

//height is (labels*(labels+spacing)*bar height + header height
$graphHeight = min(900,max(600,(count($groupLabels)+1)*count($highlightGroup->codes)*30+100));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Highlights - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
    <script src="js/amcharts3/amcharts.js"></script>
    <script src="js/amcharts3/serial.js"></script>
    <script src="js/amcharts3/plugins/export/export.min.js"></script>
    <script src="js/graph.js"></script>
    <script src="js/datatable.js"></script>
    <script>
        $(function() {
            mainTitle = <?php echo json_encode($highlightGroup->title); ?>;
            groupTitle = <?php echo json_encode($groupTitle); ?>;
            groupSummary = <?php echo json_encode($groupSummary); ?>;
            mainLabels = <?php echo json_encode($mainLabels); ?>;
            groupLabels = <?php echo json_encode($groupLabels); ?>;
            counts = <?php echo json_encode($counts); ?>;
            percentData = <?php echo json_encode($percentData); ?>;
            totals = <?php echo json_encode($variableTotals); ?>;
            tooltips = <?php echo json_encode($tooltips); ?>;
            isGrouped = groupLabels.length > 1;

            //Inputs, used to set links
            year = <?php echo json_encode($year); ?>;
            dataset = <?php echo json_encode($dataset); ?>;
            category = Number(<?php echo json_encode($cat); ?>);
            group = <?php echo json_encode($grp); ?>;
            pyramid = <?php echo json_encode($pyramid); ?>;

            //hide some inputs based on dataset or category
            if(dataset === '6th') {
                $(".hide6").hide();
            }
            if(category === 5)
                $("#gradeButton").hide(); //vehicle safety

            //change grouping box based on dataset and year
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
                splitLabel.next().next().css("margin-left", "104px");
                splitLabel.after("<br>");
            }

            if(percentData.length > 0) {
                createBarGraph(percentData, mainTitle, groupSummary, groupLabels, tooltips);

                if (!isGrouped)
                    createSimpleHighlightTable($('#datatable'), mainLabels, percentData, totals);
                else
                    createCrosstabHighlightTable($('#datatable'), mainTitle, groupTitle, mainLabels, groupLabels, percentData, totals);
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
            $('#grouping').buttonset();
            $('#grouping :input').click(function() {
                window.location = generateHighlightLink(year, dataset, category, this.value, pyramid);
            });
            $('[data-toggle="tooltip"]').tooltip();

            //set category links, preserve year and dataset, reset grouping
            $('.categories li a').each(function(){
                $(this).attr('href', generateHighlightLink(year, dataset, $(this).data('category'), '', pyramid));
            });
        });
        function changeYear(yr) {
            window.location = generateHighlightLink(yr, dataset, category, '', pyramid);
        }
        function changeDataset(ds) {
            window.location = generateHighlightLink(year, ds, category, '', pyramid);
        }
        function changePyramid(pyramid) {
            window.location = generateHighlightLink(year, dataset, category, '', pyramid);
        }
        function exportCSV() {
            if(!isGrouped)
                simpleHighlightCSV(mainTitle, mainLabels, percentData, totals, year, dataset, pyramid);
            else
                crosstabHighlightCSV(mainTitle, groupTitle, mainLabels, groupLabels, percentData, totals, year, dataset, pyramid);
        }
        function exportGraph() {
            exportToPDF(chart, mainTitle, groupTitle, year, dataset, null);
        }
        //create a link to highlights page based on current year, dataset, category, and group variables
        function generateHighlightLink(yr, ds, cat, grp, pyramid){
            if(ds === '6th' && grp === 'I2')
                return "highlights.php?year="+yr+"&ds="+ds+"&cat="+cat+"&pyr="+pyramid;
            else
                return "highlights.php?year="+yr+"&ds="+ds+"&cat="+cat+"&grp="+grp+"&pyr="+pyramid;
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div class="dataset-controls">
                <p class="shadowdeep" style="font-size: 18px; margin-top: 15px;">Showing highlights for</p>
                <div>
                    <label for="yearSelect" class="categories">Year:</label>
                    <select id="yearSelect" class="selector" onchange="changeYear(this.value)" title="Change year drop down">
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                        <option value="2019">2019</option>
                        <option value="2018">2018</option>
                        <option value="2017">2017</option>
                        <option value="2016">2016</option>
                        <option value="2015">2015</option>
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
                    <li><a data-category="1">Alcohol</a></li>
                    <li><a data-category="2">Tobacco</a></li>
                    <li><a data-category="3">Drugs</a></li>
                    <li><a data-category="20">Vaping</a></li>
                    <li class="hide6"><a data-category="4">Sexual Health</a></li>
                    <li class="hide6"><a data-category="5">Vehicle Safety</a></li>
                    <li><a data-category="6">Bullying and Cyberbullying</a></li>
                    <li class="hide6"><a data-category="7">Dating Aggression</a></li>
                    <li><a data-category="8">Harassment and Aggressive Behaviors</a></li>
                    <li><a data-category="10">Nutrition</a></li>
                    <li><a data-category="14">Physical Activity</a></li>
                    <li><a data-category="11">Mental Health</a></li>
                    <li><a data-category="12">Civic Engagement and Time Use</a></li>
                    <li><a data-category="13">Assets that Build Resiliency</a></li>
                </ul>
        </div>
        <div class="col-md-9 mainbar">
            <div style="text-align: center;">
                <h2 id="graphTitle"></h2>
                <div id="explanation" style="max-width:800px; margin: 0 auto"><?php echo $highlightGroup->explanation;?></div>
                <p class="hideIfNoGraph"><b>Mouse over</b> the graph's labels and bars to see in more detail what each element represents.</p>
                <div class="showIfNoGraph" style="font-size: 1.3em; margin-top: 20px; display: none">
                    The survey did not ask about this topic in <?php echo $year ?>. Please select a different year, grade level, or topic.
                </div>
            </div>

            <div id="grouping" class="groupbox hideIfNoGraph" style="width: max-content; margin: 20px auto 0; padding-right: 20px">
                <span style="font-weight: bold">Group data by:</span>
                <input id="none" name="grouping" type="radio" value="" checked="checked"/><label for="none">None</label>
                <span id="gradeButton" class="hide6"><input id="grade" name="grouping" type="radio" value="I2"/><label for="grade">Grade</label></span>
                <input id="gender" name="grouping" type="radio" value="I3"/><label for="gender">Gender</label>
                <?php if($pyramid == ''): ?><input id="race" name="grouping" type="radio" value="race_eth"/><label for="race">Race/Ethnicity</label>
                <?php else: ?><input id="raceSimple" name="grouping" type="radio" value="race"/><label for="raceSimple">Race (simplified)</label><?php endif; ?>
                <?php if($year >= 2021 && $dataset == "8to12"){ ?><input id="transgender" name="grouping" type="radio" value="I3A"/><label for="transgender">Transgender Status</label><?php } ?>
                <?php if($year >= 2023){ ?><input id="disability" name="grouping" type="radio" value="disability_cat"/><label for="disability">Disability</label><?php } ?>
                <div id="groupTooltip" class="tipbutton" style="margin:0 0 3px 17px"  data-toggle="tooltip" data-placement="top" title="You can separate students by grade, gender, race/ethnicity, transgender status, or disability to see how each group answered."></div>
            </div>
            <div style="overflow: visible; height: 1px; width: 100%; text-align: right" class="hideIfNoGraph">
                <input type="button" onclick="exportGraph()" value="Export to PDF" class="btn btn-blue" style="position: relative; z-index: 100">
            </div>
            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>

            <div style="text-align: center; margin-bottom: 20px;" class="hideIfNoGraph">
                <h3>Data Table<div class="tipbutton" style="margin-left:15px" data-toggle="tooltip" data-placement="top" title="This table shows the number of students in each category. To save this data, click Export to CSV."></div></h3>
                <table id="datatable" class="datatable" style="margin: 0 auto; text-align: right; border:none">
                </table>
                <?php if($grp == 'I3') { ?>
                    <p style="font-style: italic">*For Gender, the Non-Binary and Other categories will not be reported here to preserve respondents’ privacy and anonymity.<br>
                        As such, the <b>Total</b> here only includes students that answered Male or Female.<br>
                        To see the total for all students, set <b>Group Data By</b> to None.</p>
                <?php } else if($grp > 0 && $grp !== 'I2') { ?>
                    <p style="font-style: italic">*The <b>Total</b> here only includes students that answered the <b>Group Data By</b> question.<br>
                        To see the total for all students, set Group Data By to None.</p>
                <?php } ?>
                <?php if($cat == 5) { ?>
                    <p style="font-style: italic">*For Vehicle Safety questions, only 12th-grade students were asked.</p>
                <?php } ?>
                <input type="button" onclick="exportCSV()" class="btn btn-blue" value="Export to CSV" style="margin-top: 10px">
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>