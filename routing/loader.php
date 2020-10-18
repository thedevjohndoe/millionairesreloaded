<?php
require_once("routes.php");

if(!isset($_SESSION['AUTHORISED'])) {
    header("Location: login.php");
    exit;
}

$uri = isset($_GET['route']) ? $_GET['route'] : "/";

foreach($routes as $route => $template) {
    if(preg_match("#^$route#", $uri)) {
        include($template);
    }
}
