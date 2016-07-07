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
            $('#slider').slick({
                dots: true,
                infinite: false,
                speed: 300
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
<div class="container" style="width:90%;">
    <div class="row" >
        <div class="h2">Welcome to the Fairfax Youth Survey</div>
        <div class="col-md-5">
            <p>The Fairfax County, VA Youth Survey is a comprehensive, voluntary, and anonymous survey of youth in grades six (6) through twelve (12).</p>
            <p>The survey is co-sponsored by the Fairfax County Board of Supervisors and the Fairfax County School Board, and has been administered for the past ten (10) years.</p>
            <p>This website allows you to learn more about Fairfax County youth – including things that put them at risk for alcohol and illicit drug use, called risk and protective factors.</p>
            <p>For more information, please see:</p>
            <ul>
                <li><a href="#">Survey Highlights Video</a></li>
                <li><a href="#">Written Survey Report</a></li>
                <li><a href="#">Highlights</a></li>
            </ul>
        </div>
        <div class="col-md-7">
            <div style="background-color:gray; padding: 0 30px;">
                <div id="slider" style="align-content: center; text-align: center">

                    <div><a href="category.php"><img src="img/kids-at-school.jpg" height="500px" style="margin: 0 auto;"></a></div>
                    <div><a href="graphs.php"><img src="img/highschool.jpg" height="500px" style="margin: 0 auto;"></a></div>
                    <div><img src="img/survey_cropped.png" height="500px" style="margin: 0 auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>