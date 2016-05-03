<?php
include_once "config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo PAGE_TITLE ?></title>
    <?php include_styles() ?>
</head>
<body>
<?php include_header(); ?>
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="h2">Fairfax Youth Survey</div>
            <p>The Fairfax County, VA Youth Survey is a comprehensive, voluntary, and anonymous survey of youth in grades six (6) through twelve (12).</p>
            <p>The survey is co-sponsored by the Fairfax County Board of Supervisors and the Fairfax County School Board, and has been administered for the past ten (10) years.</p>
            <p>This website allows you to learn more about Fairfax County youth – including things that put them at risk for alcohol and illicit drug use, called risk and protective factors.</p>
            <p>For more information, please see:</p>
            <div class="borderarea">
                <a href="#" class="fatlink">Survey Highlights Video</a>
                <a href="#" class="fatlink">Written Survey Report</a>
                <a href="#" class="fatlink">Highlights</a>
            </div>
        </div>
        <div class="col-md-4">
            <img src="img/survey2014.png" width="100%">
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>