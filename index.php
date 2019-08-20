<?php
include_once "config/config.php";
$year = getCurrentYear();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
    <link rel="stylesheet" type="text/css" href="js/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="js/slick/slick-theme.css"/>
    <script type="text/javascript" src="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#carousel').carousel({
                interval: 6000
            });
        });
    </script>
</head>
<body>
<style type="text/css">
    .slick-arrow, .slick-prev {
        background-color: gray;
    }
    p  {
        font-size: 15px;
    }
</style>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row" style="height:630px;background-color: #2e6da4">
        <div style="width:570px; margin: -10px auto 10px;">
            <img src="img/fairfax-logo.png" height="100px" style="float: left; padding-right:20px" alt="Fairfax County Youth Survey Logo">
            <h1 class="shadowdeep" style="color:#ffffff; padding: 10px 0 10px 0;"><?php echo $year;?> Survey Highlights and Data Explorer</span></h1>
        </div>

        <div id="carousel" class="carousel slide" data-ride="carousel" >
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <li data-target="#carousel" data-slide-to="0" class="active"></li>
                <li data-target="#carousel" data-slide-to="1"></li>
                <li data-target="#carousel" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="item active">
                    <a href="highlights.php"><img src="img/olderkids2018.jpg" alt="High school kids smiling"></a>
                    <div class="carousel-caption">View the <span style="color:#dd9a3d">HIGHLIGHTS</span> of the <?php echo $year;?> survey!</div>
                </div>
                <div class="item">
                    <a href="graphs.php"><img src="img/sixthgrade2018.jpg" alt="Children smiling in a circle"></a>
                    <div class="carousel-caption">Explore individual question data!</div>
                </div>
                <div class="item">
                    <a href="http://www.fairfaxcounty.gov/youthsurvey" target="_blank"><img src="img/report2018.jpg" alt="Cover of Fairfax County Youth Survey report"></a>
                    <div class="carousel-caption">Access the full written report.</div>
                </div>
            </div>
            <a class="left carousel-control" href="#carousel" data-slide="prev" >
                <span class="glyphicon glyphicon-chevron-left"></span><span style="display:none">Previous Image</span>
            </a>
            <a class="right carousel-control" href="#carousel" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span><span style="display:none">Next Image</span>
            </a>
        </div>
    </div>
    <div style="max-width: 1050px; margin: 20px auto;">
        <div class="row">
            <div class="col-md-5">
                <h2 style="color:#767676">Fairfax County Youth Survey Interactive Data Explorer</h2>
            </div>
            <div class="col-md-7">
                <p>The <b>interactive data explorer</b> allows you to generate custom graphs and data tables on the questions and demographics that you find most interesting.</p>
                <ul>
                    <li>Head to <b><a href="highlights.php">Survey Highlights</a></b> to see selected results from various topics.</li>
                    <li><b><a href="graphs.php">Explore the Data</a></b> to create and export your own graphs from any question in the survey.</li>
                    <li>View <b><a href="trends.php">Trends over Time</a></b> to see how survey responses vary by year.</li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <h2 style="color:#767676">New Vaping and Prescription<br>Pain Reliever Questions Added</h2>
            </div>
            <div class="col-md-7">
                <p>Two new sets of questions have been added to the survey for 8th to 12th grade students.<br>
                    See <b><a href="highlights.php?cat=20">Vaping in Survey Highlights</a></b> to view lifetime and past month vape use.<br>
                    To view <b>prescription pain reliever use without a doctor's order</b>, visit <b><a href="graphs.php">Explore the Data</a></b>
                    and select the Drugs category.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <h2 style="color:#767676">6th Grade Survey Data</h2>
            </div>
            <div class="col-md-7">
                <p>The Fairfax County Youth Survey is administered in two forms: one for 8th to 12th grade students, and another for 6th grade students.<br>
                    You can access the <b>6th grade data set</b> by selecting '6th grade' at the top of
                    <b><a href="highlights.php?ds=6th">Survey Highlights</a></b>, <b><a href="graphs.php?ds=6th">Explore the Data</a></b>, or
                    <b><a href="trends.php?ds=6th">Trends over Time</a></b>.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <h2 style="color:#767676">Learn More About the Survey</h2>
            </div>
            <div class="col-md-7">
                <p>The Fairfax County, VA Youth Survey is a comprehensive, voluntary, and anonymous survey of youth in sixth, eighth, tenth, and twelfth grades.
                    It examines behaviors, experiences, and other factors that influence the health and well-being of the county's youth.
                    The survey is co-sponsored by the Fairfax County Board of Supervisors and the Fairfax County School Board, and has been administered since 2001.</p>
                <p>For more information, please see the <a href="http://www.fairfaxcounty.gov/youthsurvey" target="_blank">Youth Survey homepage</a>.</p>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>