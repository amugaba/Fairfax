<?php
/**
 * Include this file at the beginning of all pages.
 *
 * It sets environment variables, starts session, and contains utility functions such as
 * importing header and footer.
 */
if(strpos($_SERVER['HTTP_HOST'], "localhost") !== false || strpos($_SERVER['HTTP_HOST'], "angstrom") !== false) {
    define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/fairfax/");
    define("HTTP_ROOT", "http://" . $_SERVER['HTTP_HOST'] . "/fairfax/");
    define("DEBUG", true);
}
else {
    define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/");
    define("HTTP_ROOT", "http://" . $_SERVER['HTTP_HOST'] . "/");
    define("DEBUG", false);
}

define("PAGE_TITLE", "Fairfax Survey");
if(DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
else {
    //ini_set('display_errors', 1);
    //ini_set('display_startup_errors', 1);
    //error_reporting(E_ERROR);
}

function include_styles() {
    $root = HTTP_ROOT;
    echo "
    <link rel='stylesheet' href='$root/css/app.css'>
    <link rel='stylesheet' href='//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css'>
    <script src='//code.jquery.com/jquery-1.10.2.min.js'></script>
    <link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>
    <script src='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
    <script src='//code.jquery.com/ui/1.11.4/jquery-ui.min.js'></script>
    ";

    if(!DEBUG) {
        echo "<!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-68365029-2\"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
        
          gtag('config', 'UA-68365029-2');
        </script>";
    }
}
function include_header() {
    include ROOT_PATH."inc/navbar.php";
}
function include_footer() {
    include ROOT_PATH."inc/footer.php";
}
function echo_self() {
    echo htmlspecialchars($_SERVER["PHP_SELF"]);
}
function getCurrentYear() {
    return 2019;
}
function getAllYears() {
    return [2015,2016,2017,2018,2019];
}