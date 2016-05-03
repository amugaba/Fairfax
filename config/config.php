<?php
/**
 * Include this file at the beginning of all pages.
 *
 * It sets environment variables, starts session, and contains utility functions such as
 * importing header and footer.
 */
//include "conn/UserVO.php";//to store the User object properly
define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] ."/TestSite/");
define("HTTP_ROOT", "http://".$_SERVER['HTTP_HOST'] ."/TestSite/");
define("PAGE_TITLE", "Fairfax Survey");

session_save_path(ROOT_PATH."session");
ini_set("session.cache_expire", 12000); //3+h
ini_set("session.gc_maxlifetime", 12000); //3+h
session_start();

function include_styles() {
    $root = HTTP_ROOT;
    echo "
    <link rel='stylesheet' href='$root/css/app.css'>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js'></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js'></script>
    <link rel=\"stylesheet\" href=\"bower_components/bootstrap/dist/css/bootstrap.css\">
    <script src='$root/bower_components/bootstrap/js/dropdown.js'></script>
    <link rel='stylesheet' href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css'>
    ";
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
/*
function include_menu() {
    include ROOT_PATH."include/training-top.php";
}

function check_login() {
    if(!isset($_SESSION['user']))
    {
        header("Location: login-redir.php");
        return false;
    }
    return true;
}
function getUserID()
{
    if(isset($_SESSION['user_sn']))
        return $_SESSION['user_sn'];
    return null;
}
function getUser()
{
    return $_SESSION['user'];
}*/