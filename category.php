<?php
require_once "config/config.php";
require_once 'hidden/DataService.php';

$ds = new DataService();
$cat = isset($_GET['cat'])? $_GET['cat'] : 1;
$grp = isset($_GET['grp'])? $_GET['grp'] : 'grade';

if($cat == 1) {
    $title = "Substance Use";
    $qCodes = ['A3A','A4','D3A'];
    $labels = ['Past-Year Alcohol','Binge Drinking','Past-Year Marijuana'];
    $lowCutoffs = [2,2,2];
    $highCutoffs = [2,2,2];
    $totalCutoffs = [0,0,0];
}
else if($cat == 2) {
    $title = "Sexual Activity";
    $qCodes = ['X1','X6','X8'];
    $labels = ['Lifetime Sexual Intercourse','Condom Use','Lifetime Oral Sex'];
    $lowCutoffs = [1,2,1];
    $highCutoffs = [1,2,1];
    $totalCutoffs = [0,1,0];
}

$newdata = []; //for each question, what percent of cases fell within the cutoff range

//get data for each question and combine at cutoff points
for($i=0; $i<count($qCodes); $i++)
{
    $data = $ds->getDataCutoff($qCodes[$i],$lowCutoffs[$i],$highCutoffs[$i], $totalCutoffs[$i], $grp);

    if($grp == 'grade') {
        $newdata[] = ['answer' => $labels[$i], 'grade8' => $data[0]['num'] * 100, 'grade10' => $data[1]['num'] * 100, 'grade12' => $data[2]['num'] * 100];
    }
    else if($grp == 'gender') {
        $newdata[] = ['answer' => $labels[$i], 'female' => $data[1]['num'] * 100, 'male' => $data[2]['num'] * 100, 'unknown' => $data[0]['num'] * 100];
    }
    else if($grp == 'race') {
        $newdata[] = ['answer' => $labels[$i], 'white' => $data[1]['num'] * 100, 'nonwhite' => $data[2]['num'] * 100, 'unknown' => $data[0]['num'] * 100];
    }
    else {
        $newdata[] = ['answer' => $labels[$i], 'total' => $data[0]['num'] * 100];
    }
}
//var_dump($newdata);
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
    <link rel="stylesheet" href="css/app.css">
    <script>
        $(function() {
            init(<?php echo json_encode($newdata); ?>);
            createPercentChart(<?php echo json_encode($grp);?>);

            $('#grouping :input[value=<?php echo $grp;?>]').prop("checked",true);
            $('#grouping').buttonset();
            $('#grouping :input').click(function() {
                window.location = "category.php?cat=<?php echo $cat;?>&grp="+this.value;
            });

        });
    </script>
</head>
<body>
<?php include_header(); ?>
<div class="container-fluid">
    <div class="h2" style="margin: 0 auto;">Graphs by Category</div>
    <div class="row">
        <div class="col-md-2">
            <div class="h4">Categories</div>
            <div class="borderarea">
                <a href='?cat=1'>Substance Use</a><br>
                <a href='?cat=2'>Sexual Activity</a><br>
                <a href='?cat=3'>Vehicle Safety</a><br>
                <a href='?cat=4'>Bullying</a><br>
                <a href='?cat=5'>Other Aggression</a><br>
                <a href='?cat=6'>Physical Activity</a><br>
                <a href='?cat=7'>Nutrition</a><br>
                <a href='?cat=8'>Mental Health</a><br>
                <a href='?cat=9'>Extracurriculars</a><br>
                <a href='?cat=10'>Civic Behavior</a><br>
                <a href='?cat=11'>Risk/Protective Factors</a><br>
                <a href='?cat=12'>Three to Succeed</a>
            </div>
        </div>
        <div class="col-md-10">
            <div style="text-align: center; font-weight:bold; font-size:14pt;"><?php echo $title; ?></div>
            <div id="grouping" class="bordergrey" style="width:500px; margin: 0 auto">
                <label class="searchLabel">Group data by:</label>
                <input id="none" name="grouping" type="radio" value="none" checked="checked"/><label for="none">None</label>
                <input id="grade" name="grouping" type="radio" value="grade"/><label for="grade">Grade</label>
                <input id="gender" name="grouping" type="radio" value="gender"/><label for="gender">Gender</label>
                <input id="race" name="grouping" type="radio" value="race"/><label for="race">Race</label>
            </div>
            <div id="chartdiv" style="width100%; height:600px;"></div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>