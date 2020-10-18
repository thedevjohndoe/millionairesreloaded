<?php
/**
 * Withdrawals template
 */
$user_id = $_SESSION['user_id'];
$profile = new profile($user_id);
$auction = new auction();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $_SESSION['errors'] = array("Withdrawals aren't allowed at this stage.");
    // header("Location: withdrawals");
    // exit;

    $alerts = array();
    $errors = array();
    $current = date("H:i:s");
    // $current = new DateTime($current);
    $morning = new DateTime(MORNING_AUCTION);
    $midday  = new DateTime(MIDDAY_AUCTION);
    $evening = new DateTime(EVENING_AUCTION);
    $morning_end = new DateTime(MORNING_AUCTION);
    $morning_end = $morning_end->add(new DateInterval("PT1H"));
    $midday__end = new DateTime(MIDDAY_AUCTION);
    $midday__end = $midday__end->add(new DateInterval("PT1H"));
    $evening_end = new DateTime(EVENING_AUCTION);
    $evening_end = $evening_end->add(new DateInterval("PT1H"));

    if(($morning->format("H:i") <= $current && $current < $morning_end->format("H:i")) || ($midday->format("H:i") <= $current && $current < $midday__end->format("H:i")) || ($evening->format("H:i") <= $current && $current < $evening_end->format("H:i"))) {
        $errors[] = "Unfortunately, withdrawals are not allowed during an auction.";
    } else {
        if($check_bids = $dbcon->prepare("SELECT COUNT(*) FROM bid WHERE user_id = ? AND confirmed = 1")) {
            $check_bids->bind_param("i", $user_id);
            $check_bids->execute();
            $check_bids->bind_result($count);
            $check_bids->fetch();
            $check_bids->close();

            if($count) {
                if($_POST['amount'] && $_POST['bank']) {
                    if(is_numeric($_POST['amount'])) {
                        $amount = $_POST['amount'];
                        $payment_id = $_POST['bank'];
                        if($check_balance = $dbcon->prepare("SELECT available, (SELECT COUNT(*) FROM sale WHERE deleted = 0 AND expired = 0 AND suspended = 0 AND user_id = user.ID) AS open_sales FROM user WHERE ID = ?")) {
                            $check_balance->bind_param("i", $user_id);
                            $check_balance->execute();
                            $check_balance->bind_result($available, $open_sales);
                            $check_balance->fetch();
                            $check_balance->close();
            
                            if($amount <= $available && $amount >= 200 && $amount <= 2000 && !$open_sales) {
                                if($create_sale = $dbcon->prepare("INSERT INTO sale (user_id, payment_id, allocated, available, comment, suspended) VALUES (?, ?, ?, allocated, 'Suspended by the Cashier', 1)")) {
                                    $create_sale->bind_param("iii", $user_id, $payment_id, $amount);
                                    $create_sale->execute();
                                    if($sale_id = $create_sale->insert_id) {
                                        $update = $dbcon->prepare("UPDATE user SET balance = balance - @withdrawal, available = available - @withdrawal WHERE ID = ? AND @withdrawal := ?");
                                        $update->bind_param("ii", $user_id, $amount);
                                        $update->execute();
                                        $update->close();
            
                                        $alerts[] = "You sale was created successfully.";
                                    }
                                } else {
                                    trigger_error($dbcon->error);
                                    $errors[] = "There was an error submitting your bid.";
                                }
                            } else {
                                if($amount > $available)
                                $errors[] = "You do not have enough coins available.";
                                elseif($amount < 200)
                                $errors[] = "A minimum of 200 coins is required per withdrawal.";
                                elseif($amount > 2000)
                                $errors[] = "A maximum of 2000 coins is allowed per withdrawal.";
                                elseif($open_sales)
                                $errors[] = "You already have a withdrawal going into the next auction. Only one withdrawal per auction allowed.";
                            }
                        } else {
                            trigger_error($dbcon->error);
                            $errors[] = "There was an error verifying your available balance.";
                        }
                    } else {
                        if(!is_numeric($_POST['amount']))
                        $errors[] = "The amount of coins to sell can only be numeric.";
                    }
                } else {
                    if(!$_POST['amount'])
                    $errors[] = "You have not specified the amount of coins you want to sell.";
                    if(!$_POST['bank'])
                    $errors[] = "No payment details selected.";
                }
            } else {
                $errors[] = "You cannot withdraw without a bidding history. At least one confirmed bid is required.";
            }
        } else {
            $errors[] = "There was an error checking bid history.";
        }
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: withdrawals");
    exit;
}
get_header();
?>

        <div class="content__container">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="content__section">
<?php check_errors_and_alerts(); ?>

                <div class="section__header">
                    <h1>Withdrawals</h1>
                </div>
                <div class="section__content">
                    <div class="sales">
                        <div class="container">
                            <div class="container__column container__column--full">
                                <div class="sale__wrap">
                                    <div class="sale__header">
                                        <p>New Withdrawal</p>
                                    </div>
                                    <form method="POST">
                                        <div class="sale__controls">
                                            <div class="control__input">
                                                <label>Amount of Coins to Sell</label>
                                                <input type="text" name="amount" value="">
                                            </div>
                                            <div class="control__select">
                                                <select name="bank">
                                                    <option value>No Bank Account Selected</option>
<?php foreach($profile->get_payment_details() as $detail): ?>

                                                    <option value="<?php echo($detail['ID']); ?>"><?php echo("$detail[bank_name] ($detail[description])"); ?></option>
<?php endforeach; ?>

                                                </select>
                                            </div>
                                            <div class="control__button">
                                                <!-- <button class="button button--sale">Sell</button> -->
                                                <input type="submit" class="button" name="sell" value="Sell Now">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sales">
                        <h2>My Withdrawals</h2>
<?php if($sales = $profile->get_sales()): ?>
                        <div class="container">
<?php foreach($sales as $sale): ?>

                            <div class="container__column container__column--auction container__column--half">
                                <ul>
                                    <li><label>Date: <?php echo($sale['created']); ?></label></li>
                                    <li><label>Allocated: <?php echo($sale['allocated']); ?></label></li>
                                    <li><label>Sold: <?php echo($sale['sold']); ?> Coins</label></li>
                                    <li class="table__column"><label>Available: <?php echo($sale['available']); ?></label></li>
                                    <li><label>Status: <?php echo(($sale['expired']) ? "Expired" : "Selling"); ?></label></li>
                                </ul>
                            </div>
    <?php endforeach; ?>

                        </div>
    <?php else: ?>

                        <p>You do not have a withdrawal history.</p>
    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
<?php
get_footer();