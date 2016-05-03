<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

$ds = new DataService();
$variables = $ds->getVariables();
$mainvar = null;

//Get labels and data for the given question
if(isset($_GET['code']))
{
    //check that code is for a valid variable
    foreach($variables as $var) {
        if($var->code == $_GET['code']) {
            $mainvar = $var;
            break;
        }
    }

    if($mainvar != null) {
        $labels = $ds->getLabels($mainvar->autoid);
        $data = $ds->getData($mainvar->code);

        //construct new data array using labels
        $newdata = [];
        for($i=1; $i<=count($labels); $i++)
        {
            $label = $labels[$i-1];

            //stop when no more labels
            if($label == '')
                break;

            //get counts from old data array
            $num = 0;
            foreach($data as $row) {
                if($row['answer'] == $i) {
                    $num = $row['num'];
                    break;
                }

            }

            $newdata[] = ['answer'=>$label, 'num'=>$num];
        }

        //add No response if there are any null values
        if($data[0]['answer'] == null)
            $newdata[] = ['answer'=>'No Response', 'num'=>$data[0]['num']];

        $mainvar->answers = $labels;
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
    <?php include_styles() ?>
    <script src="js/amcharts/amcharts.js" type="text/javascript"></script>
    <script src="js/amcharts/serial.js" type="text/javascript"></script>
    <script src="js/tables.js" type="application/javascript"></script>
    <script>
        $(function() {
            <?php if($mainvar != null) { ?>
                init(<?php echo json_encode($mainvar); ?>, <?php echo json_encode($newdata); ?>);

            <?php } ?>
            createChart();

        });
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container">
    <div class="h2" style="margin: 0 auto;">Graphs by Category</div>
    <div class="row">
        <div class="col-md-4">
            <div class="h4">Categories</div>
            <div class="borderarea">
                <?php foreach($variables as $var) {
                    echo "<a href='?code=$var->code'>$var->question</a><br>";
                } ?>
            </div>
        </div>
        <div class="col-md-8">
            <div style="text-align: center; font-weight:bold; font-size:14pt;"><?php echo $mainvar->question; ?></div>
            <div id="chartdiv" style="width100%; height:600px;"></div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>