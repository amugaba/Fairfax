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
    <script src="js/amcharts/plugins/export/export.js" type="text/javascript"></script>
    <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css">
    <script src="js/crosstab.js" type="application/javascript"></script>
    <script>
        $(function() {
            chart = AmCharts.makeChart("chartdiv",{
                "export": {
                    "enabled": true,
                    "menu": []
                },
                "type": "serial",
                "categoryField": "category",
                "categoryAxis": {
                    "gridPosition": "start"
                },
                "graphs": [
                    {
                        "title": "Graph title",
                        "valueField": "column-1"
                    }
                ],
                "valueAxes": [
                    {
                        "title": "Axis title"
                    }
                ],
                "legend": {
                    "useGraphSettings": true
                },
                "titles": [
                    {
                        "size": 15,
                        "text": "Chart Title"
                    }
                ],
                "dataProvider": [
                    {
                        "category": "category 1",
                        "column-1": 8
                    },
                    {
                        "category": "category 2",
                        "column-1": 10
                    },
                ]
            });
        });

        function exportGraph() {
            var pdf_layout = {
                content: [
                    {
                        text: "2015 Fairfax County Youth Survey",
                        style: ["header","safetyDistance"]
                    },
                    {
                        image: "image_1",
                        fit: [400,400]
                    }
                ],
                images: {
                },
                styles: {
                    header: {
                        fontSize: 18,
                        bold: true,
                        alignment: "center"
                    },
                    subheader: {
                        bold: true
                    },
                    description: {
                        fontSize: 10,
                        color: "#CCCCCC",
                        margin: [ 0, 5, 0, 10 ]
                    },
                    safetyDistance: {
                        margin: [0, 0, 0, 20]
                    }
                }
            }

            chart.export.capture( {}, function() {
                this.toPNG({multiplier: 2},
                    function( data ) {
                        pdf_layout.images["image_1"] = data;
                        this.toPDF(pdf_layout, function (data) {
                            this.download(data, this.defaults.formats.PDF.mimeType, "fairfaxgraph.pdf");
                        });
                    });
            });
        }
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row">
        <div class="col-md-9">
            <div style="text-align: center;">
                <div id="graphTitle"></div>
            </div>
            <input type="button" onclick="exportGraph()" value="Export">

            <div id="chartdiv" style="width100%; height:<?php echo $graphHeight;?>px;"></div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>