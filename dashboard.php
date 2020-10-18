<?php
require_once("configuration.php");

if(!isset($_SESSION['AUTHORISED']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 0)) {
    header("Location: ./");
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GGD Dashboard</title>
</head>
<style>
*,
*::before,
*::after {
    border: 0;
    margin: 0;
    padding: 0;
    outline: none;
    box-sizing: border-box;
}
html {
    font-size: 62.5%;
    line-height: 1.5;
}
body {
    font-size: 1.6rem;
    font-family: sans-serif;
}
ul,
ol {
    list-style: none;
}
h1 {
    font-size: 4.8rem;
}
h2 {
    font-size: 3.6rem;
}
.site {
    width: 100%;
    height: 100vh;
}
.site .site__content {
    width: 100%;
    height: 100%;
    display: block;
    position: relative;
}
.site .site__content .container {
    width: 100%;
    height: 100%;
    display: flex;
    padding: 40px;
    justify-content: space-between;
}
.site .site__content .container .container__column {
    width: calc(25% - 10px);
    height: 100%;
    border: 1px solid rgb(225, 225, 225);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    padding: 40px;
}
.site .site__content .container .container__column .column__header {
    width: 100%;
    display: block;
    margin-bottom: 20px;
}
.list {
    width: 100%;
    height: 100%;
    padding: 10px;
    overflow-y: scroll;
    background: rgb(251, 251, 251);
}
.list .list__item {
    padding: 5px 0px;
}

@media screen and (max-width: 768px) {
    .site .site__content .container {
        display: block;
    }
    .site .site__content .container .container__column {
        width: 100%;
        height: 100%;
        margin-bottom: 40px;
    }
}
</style>
<body>
<div id="site" class="site">
    <div class="site__content">
        <div class="container">
            <div class="container__column">
                <div class="column__header">
                    <h2>Users</h2>
                </div>
                <ul class="list">
<?php
if($users = $dbcon->prepare("SELECT CONCAT('USER', LPAD(ID, 4, 0)) AS user_id, user.* FROM user WHERE disabled = 0")) {
    $users->execute();
    if($result = $users->get_result()) {
        while($user = $result->fetch_assoc()):
?>
                    <li class="list__item"><?php echo("$user[user_id]: $user[firstname] $user[surname] (Username: $user[username])"); ?></li>
<?php
        endwhile;
    }
    $users->close();
} else trigger_error($dbcon->error);
?>
                </ul>
            </div>
            <div class="container__column">
                <div class="column__header">
                    <h2>Withdrawals</h2>
                </div>
                <ul class="list">
<?php
if($sales = $dbcon->prepare("SELECT sale.ID, sale.allocated, sale.available, sale.suspended, CONCAT(user.firstname, ' ', user.surname) AS seller, user.is_admin, sale.user_id AS seller_id, CONCAT('GGD', LPAD(sale.ID, 4, 0)) AS reference, payment.bank_name FROM sale LEFT JOIN user ON user.ID = sale.user_id LEFT JOIN payment ON payment.ID = sale.payment_id WHERE sale.expired = 0 AND sale.deleted = 0 ORDER BY sale.available DESC")) {
    $sales->execute();
    if($result = $sales->get_result()) {
        while($sale = $result->fetch_assoc()):
            $suspension = ($sale['suspended']) ? "[SUSPENDED]" : "[ACTIVE]";
            if($sale['is_admin']):
?>
                    <li class="list__item"><?php echo("Lot $sale[ID]: $sale[available] of $sale[allocated] coins on sale by $sale[seller] <strong>[ADMIN]</strong> $suspension"); ?></li>
<?php
            else:
?>
                    <li class="list__item"><?php echo("Lot $sale[ID]: $sale[available] of $sale[allocated] coins on sale by $sale[seller] $suspension"); ?></li>
<?php
            endif;
        endwhile;
    }
    $sales->close();
} else trigger_error($dbcon->error);
?>
                </ul>
            </div>
            <div class="container__column">
                <div class="column__header">
                    <h2>Pending Bids</h2>
                </div>
                <ul class="list">
<?php
if($bids = $dbcon->prepare("SELECT bid.ID, bid.user_id, CONCAT(user.firstname, ' ', user.surname) AS bidder, bid.sale_id, sale.user_id AS seller_id, bid.term_id, bid.created, bid.offer, bid.interest, bid.confirmed, term.duration, term.interest_rate FROM bid LEFT JOIN sale ON sale.ID = bid.sale_id LEFT JOIN user ON user.ID = bid.user_id LEFT JOIN term ON term.ID = bid.term_id WHERE bid.deleted = 0 AND bid.confirmed = 0")) {
    $bids->execute();
    if($result = $bids->get_result()) {
        while($bid = $result->fetch_assoc()):
?>
                    <li class="list__item"><?php echo("$bid[offer] offered on Lot $bid[sale_id] by $bid[bidder] ($bid[user_id])"); ?></li>
<?php
        endwhile;
    }
    $bids->close();
} else trigger_error($dbcon->error);
?>
                </ul>
            </div>
            <div class="container__column">
                <div class="column__header">
                    <h2>Maturity</h2>
                </div>
                <ul class="list">
<?php
if($bids = $dbcon->prepare("SELECT bid.ID, bid.user_id, bid.term_id, bid.created, CONCAT(user.firstname, ' ', user.surname) AS bidder, bid.offer, bid.interest, (bid.offer + bid.interest) AS total_return, bid.confirmed, term.duration, term.interest_rate, bid.mature, DATE_FORMAT(DATE_ADD(bid.created, INTERVAL term.duration DAY), '%d %M %Y %H:%i:%s') AS maturity_date FROM bid LEFT JOIN user ON user.ID = bid.user_id LEFT JOIN term ON term.ID = bid.term_id WHERE DATE(bid.created) > '2020-08-31' AND bid.deleted = 0 AND bid.confirmed = 1 AND bid.mature = 0 AND TIMESTAMPDIFF(DAY, bid.created, CURRENT_TIMESTAMP + INTERVAL 1 DAY) >= term.duration")) {
    $bids->execute();
    if($result = $bids->get_result()) {
        while($bid = $result->fetch_assoc()):
?>
                    <li class="list__item"><?php echo("<strong>$bid[total_return] total return</strong> for $bid[bidder], maturing $bid[maturity_date]"); ?></li>
<?php
        endwhile;
    }
    $bids->close();
} else trigger_error($dbcon->error);
?>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>
