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
<div class="container" id="main">
    <div class="row" style="background-color: #2e6da4">
        <div style="width:570px; margin: -10px auto 10px;">
            <img src="img/fairfax-logo3.png" height="100px" style="float: left; padding-right:20px">
            <div class="h1 shadowdeep" style="color:#ffffff; padding: 10px 0 10px 0;">2015 Survey Highlights and Data Explorer</span></div>
        </div>

    </div>
    <div style="max-width: 1000px; margin: 20px auto;">
        <div class="row">
            <div class="col-md-3">
                <div class="h2 " style="color:#767676">Contact Us</div>
            </div>
            <div class="col-md-9">
                <p>For inquiries regarding the Fairfax Youth Survey or the Data Explorer website, please contact
                    <a href="mailto:NCS-Prevention@fairfaxcounty.gov">NCS-Prevention@fairfaxcounty.gov</a></p>
            </div>
        </div>
    </div>
</div>
<?php include_footer(); ?>
</body>
</html>