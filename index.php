<?php
include_once "config/config.php";
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
</style>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row title" style="padding-top: 20px; padding-bottom: 20px">
        <div class="col-sm-6" style="color: white">
            <div style="text-align: center; margin: 0 0 0 auto">
                <div style="margin-top: 30px;">
                    <img src="img/fairfaxlogo.png"  alt="Fairfax County Logo">
                </div>
                <h1 class="shadowdeep" style="margin-top: 20px">2022&ndash;2023 Youth Survey Interactive Data Explorer</h1>
                <div style="margin-top: 40px">
                    <h2 style="max-width: 700px; margin: 0 auto">Generate custom graphs and data tables on the questions you find most interesting!</h2>
                    <a href="highlights.php" class="button-link" style="margin: 30px auto">Check Out the Highlights</a>
                    <p style="font-size: 22px">Or build your own graphs at:<br> <a href="graphs.php" class="text-link">Explore the Data</a>,
                        <a href="trends.php" class="text-link">Trends Over Time</a>, and <a href="three-to-succeed.php" class="text-link">Three to Succeed</a>.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div style="text-align: left">
                <img src="img/olderkids2021.jpg" alt="High school kids smiling">
            </div>
        </div>
    </div>
    <div class="row" style="padding-top: 20px; max-width: 1050px; margin: 0 auto; font-size: 16px">
        <h1 style="text-align: center">Learn More About the Survey and Data Explorer</h1>
        <div class="grid">
            <div class="grid-third">
                <div style="margin: 10px">
                    <div class="figure" style="margin-bottom: 10px">
                        <img alt="Open books" src="img/tablet-graph.png" style="width: 100%">
                    </div>
                    <h2>Data Explorer Features</h2>
                    <p><b><a href="highlights.php">Survey Highlights</a></b> shows selected results from various topics.</p>
                    <p><b><a href="graphs.php">Explore the Data</a></b> lets you create a graph from any question in the survey.</p>
                    <p><b><a href="trends.php">Trends Over Time</a></b> shows how survey responses vary by year.</p>
                    <p><b><a href="three-to-succeed.php">Three to Succeed</a></b> displays how survey responses vary with a student's number of protective assets.</p>
                </div>
            </div>
            <div class="grid-third">
                <div style="margin: 10px">
                    <div class="figure" style="margin-bottom: 10px">
                        <img alt="Open books" src="img/keyboard-survey.jpg" style="width: 330px; height: 283px;">
                    </div>
                    <h2>New Survey Items</h2>
                    <p>No new questions were added in this year's survey.</p><p></p>However, trends are now available for questions that were added in the previous year,
                        such as falling asleep while driving, past month hookah use, overall vegetable consumption, and stress level.</p>
                </div>
            </div>
            <div class="grid-third">
                <div style="margin: 10px">
                    <div class="figure" style="margin-bottom: 10px">
                        <img alt="Open books" src="img/sixthgrade2021-square.jpg" style="width: 100%">
                    </div>
                    <h2>6th Grade Survey</h2>
                    <p>The Fairfax County Youth Survey is administered in two forms: one for 8th to 12th grade students, and another for 6th grade students.</p>
                    <p>You can access the <b>6th grade data set</b> by selecting '6th grade' at the top of
                        <b><a href="highlights.php?ds=6th">Survey Highlights</a></b>, <b><a href="graphs.php?ds=6th">Explore the Data</a></b>,
                        <b><a href="trends.php?ds=6th">Trends over Time</a></b>, or <b><a href="three-to-succeed.php?ds=6th">Three to Succeed</a></b>.</p>
                </div>
            </div>
        </div>
    </div>
    <div style="max-width: 1050px; margin: 20px auto;">
        <div class="row">
            <div class="col-md-12">
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <h2 style="color:#767676">About the Survey</h2>
            </div>
            <div class="col-md-9" style="font-size: 16px">
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