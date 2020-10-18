<?php
require_once("../configuration.php");

if(!isset($_SESSION['is_admin'])) {
    $_SESSION['alerts'] = array("Your profile is not authorised to view the dashboard.");
    header("Location: ../profile");
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div class="site">

</div>
</body>
</html>