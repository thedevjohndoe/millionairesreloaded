<?php
/**
 * Loads template header
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Global Goal Diggers</title>
    <link rel="stylesheet" href="assets/scss/index.css?v=<?php echo(time()); ?>">
    <script src="assets/js/index.js"></script>
</head>
<body>
<div class="site">
    <!-- <header class="site__header">
        <div class="header header--sticky">
            <div class="header__logo">
                <h1><a href="./">Global Goal Diggers</a></h1>
            </div>
            <div class="header__menu">
                <nav>
                    <a href="./profile">Profile</a>
                    <a href="./wallet">Wallet</a>
                    <a href="login.php?action=logout">Logout</a>
                </nav>
            </div>
        </div>
    </header> -->
    <div class="content">
        <div id="content__navbar" class="content__navbar">
            <div class="rollback"></div>
            <div class="navbar__user">
                <span>Hi, <a href="profile"><?php echo($_SESSION['user_firstname']); ?></a>! (<a href="login.php?action=logout">Logout</a>)<br/>
                <a href="messages">You have 0 messages</a></span>
            </div>
            <ul class="navbar__menu">
                <li class="menu__item">
                    <a class="item__link" href="./">Dashboard</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="auction">Auction</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="bids">Bids</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="goal">Goal</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="wallet">Wallet</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="withdrawals">Withdrawals</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="payments">Payments</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="notifications">Notifications</a>
                </li>
                <li class="menu__item">
                    <a class="item__link" href="referrals">Referrals</a>
                </li>
            </ul>
        </div>
