<?php
/**
 * Include this file at the beginning of all pages.
 *
 * It sets environment variables, starts session, and contains utility functions such as
 * importing header and footer.
 */
if(strpos($_SERVER['HTTP_HOST'], "localhost") !== false) {
    define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/fairfax/");
    define("HTTP_ROOT", "http://" . $_SERVER['HTTP_HOST'] . "/fairfax/");
    define("DEBUG", true);
}
else if(strpos($_SERVER['HTTP_HOST'], "angstrom") !== false) {
    define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/fairfax/");
    define("HTTP_ROOT", "https://" . $_SERVER['HTTP_HOST'] . "/fairfax/");
    define("DEBUG", true);
}
else {
    define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/");
    define("HTTP_ROOT", "https://" . $_SERVER['HTTP_HOST'] . "/");
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

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

function include_styles(): void
{
    $root = HTTP_ROOT;
    echo "<link rel='stylesheet' href='//code.jquery.com/ui/1.14.2/themes/smoothness/jquery-ui.min.css'>
    <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>
    <link rel='stylesheet' href='$root/css/app.css'>";
    //TBD - change Bootstrap to https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css

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

function include_js(): void
{
    $root = HTTP_ROOT;
    echo "<script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
        <script src='//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
        <script src='//code.jquery.com/ui/1.14.2/jquery-ui.min.js'></script>
        <script src='$root/js/amcharts3/amcharts.js'></script>
        <script src='$root/js/amcharts3/serial.js'></script>
        <script src='$root/js/amcharts3/plugins/export/export.min.js'></script>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css' rel='stylesheet'/>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.full.js'></script>
        <script src='$root/js/variableSelector.js'></script>
        <script src='$root/js/graph.js'></script>
        <script src='$root/js/datatable.js'></script>";
}

function include_header(): void
{
    include ROOT_PATH."inc/navbar.php";
}
function include_footer(): void
{
    include ROOT_PATH."inc/footer.php";
}
function echo_self(): void
{
    echo htmlspecialchars($_SERVER["PHP_SELF"]);
}
function getCurrentYear(): int
{
    return 2025;
}
function getAllYears(): array
{
    return [2015,2016,2017,2018,2019,2021,2022,2023,2024,2025];
}
function getAllYearsReversed(): array
{
    return array_reverse(getAllYears());
}

/**
 * Get input and convert unassigned and empty string to null
 * @param string $key
 * @return mixed
 */
function getInput(string $key): mixed
{
    if(($_GET[$key] ?? null) == null || $_GET[$key] === '')
        return null;
    return $_GET[$key];
}