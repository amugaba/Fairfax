<?php
include_once "config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact - Fairfax County Youth Survey</title>
    <?php include_styles() ?>
</head>
<body>
<?php include_header(); ?>
<div class="container" id="main">
    <div class="row" style="background-color: #2e6da4">
        <div style="width:570px; margin: -10px auto 10px;">
            <img src="img/fairfax-logo.png" height="100px" style="float: left; padding-right:20px" alt="Fairfax County Youth Survey logo">
            <h1 class="shadowdeep" style="color:#ffffff; padding: 10px 0 10px 0;"><?php echo getCurrentYear();?> Survey Highlights and Data Explorer</span></h1>
        </div>

    </div>
    <div style="max-width: 1000px; margin: 20px auto;">
        <div class="row">
            <div class="col-md-3">
                <h2 style="color:#767676">Contact Us</h2>
            </div>
            <div class="col-md-9">
                <p>For inquiries regarding the Fairfax County Youth Survey or the Data Explorer website, please contact
                    <a href="mailto:OSMDataAnalytics@fairfaxcounty.gov">OSMDataAnalytics@fairfaxcounty.gov</a></p>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>