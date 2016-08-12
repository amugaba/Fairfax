<?php
include_once "config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
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
</style>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row" style="height:630px;background-color: #2e6da4">
        <div style="width:550px; margin: -10px auto 10px;">
            <img src="img/fairfax-logo3.png" height="100px" style="float: left; padding-right:20px">
            <div class="h1 shadow" style="color:#ffffff; padding: 10px 0 10px 0;">2015 Survey Results and Highlights</span></div>
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
                    <a href="category.php"><img src="img/youngkids.jpg"></a>
                    <div class="carousel-caption">View the <span style="color:#dd9a3d">HIGHLIGHTS</span> of the 2015 survey!</div>
                </div>
                <div class="item">
                    <a href="graphs.php"><img src="img/olderkids.jpg"></a>
                    <div class="carousel-caption">Explore individual question data!</div>
                </div>
                <div class="item">
                    <a href="http://www.fairfaxcounty.gov/demogrph/youth_survey/pdfs/sy2014_15_youth_survey_report.pdf" target="_blank"><img src="img/survey2014.jpg"></a>
                    <div class="carousel-caption">Access the full written report.</div>
                </div>
            </div>
            <a class="left carousel-control" href="#carousel" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <a class="right carousel-control" href="#carousel" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div>
    </div>
    <div style="max-width: 1000px; margin: 20px auto;">
        <div class="row">
            <div class="col-md-5">
                <div class="h2 " style="color:#767676">Fairfax Youth Survey Interactive Data Explorer</div>
            </div>
            <div class="col-md-7">
                <p>The <b>interactive data explorer</b> allows you to generate custom graphs and data tables on the questions and demographics that you find most interesting.</p>
                <ul>
                    <li>Head to <b><a href="category.php">Survey Highlights</a></b> to see a hand-picked selection of the most important findings.</li>
                    <li>Or <b><a href="graphs.php">Explore All Questions</a></b> to create and export your own graphs from any questions in the survey.</li>
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
                <div class="h2 " style="color:#767676">Learn More About the Survey</div>
            </div>
            <div class="col-md-7">
                <p>The Fairfax County, VA Youth Survey is a comprehensive, voluntary, and anonymous survey of youth in grades six through twelve.
                    It examines behaviors, experiences, and other factors that influence the health and well-being of the county's youth.
                    The survey is co-sponsored by the Fairfax County Board of Supervisors and the Fairfax County School Board, and has been administered for the past ten (10) years.</p>
                <p>For more information, please see the <a href="http://www.fairfaxcounty.gov/demogrph/youth_survey_results.htm" target="_blank">Youth Survey homepage</a>.</p>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>