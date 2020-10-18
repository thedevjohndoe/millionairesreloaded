<?php
/**
 * Load application files
 */
global $dbcon;

include("app/functions.php");
include("app/templates.php");
include("app/profile.php");
include("app/auction.php");
include("database/dbcon.php");

function start_database() {
    global $dbcon;

    if(isset($dbcon)) {
        return;
    }

    $dbhost = defined("DB_HOST") ? DB_HOST : "";
    $dbuser = defined("DB_HOST") ? DB_USER : "";
    $dbpass = defined("DB_HOST") ? DB_PASS : "";
    $dbname = defined("DB_HOST") ? DB_NAME : "";

    $dbcon = new dbcon($dbhost, $dbuser, $dbpass, $dbname);
}

// start_database();