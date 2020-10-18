<?php
/**
 * Sales template
 */
$user_id = $_SESSION['user_id'];
$profile = new profile($user_id);
$auction = new auction();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_POST['bid'] && $_POST['offer']) {
        if(is_numeric($_POST['bid']) && is_numeric($_POST['offer'])) {
            $bid_id = $_POST['bid'];
            $offer  = $_POST['offer'];

            if($check_deposit = $dbcon->prepare("SELECT confirmation FROM bid WHERE confirmed = 1 AND ID = ?")) {
                $check_deposit->bind_param("i", $bid_id);
                $check_deposit->execute();
                $check_deposit->bind_result($confirmation);
                $check_deposit->fetch();
                $check_deposit->close();

                if($confirmation) {
                    $errors[] = "The bid you are trying to confirm was confirmed on $confirmation.";
                } else {
                    if($confirm = $dbcon->prepare("UPDATE bid SET confirmed = 1, confirmation = CURRENT_TIMESTAMP() WHERE ID = ?")) {
                        $confirm->bind_param("i", $bid_id);
                        $confirm->execute();
                        if($confirm->affected_rows) {
                            // Check if user was referred
                            if($check_referral = $dbcon->prepare("SELECT referral_code FROM referral WHERE referred_id = (SELECT user_id FROM bid WHERE ID = ?) LIMIT 1")) {
                                $check_referral->bind_param("i", $bid_id);
                                $check_referral->execute();
                                $check_referral->bind_result($referral_code);
                                $check_referral->fetch();
                                $check_referral->close();

                                // If referred,
                                if($referral_code) {
                                    $incentive = round(($offer / 100) * 10);
                                    if($bonus = $dbcon->prepare("INSERT INTO bonus (user_id, bid_id, incentive) VALUES ((SELECT referrer_id FROM referral WHERE referral_code = ?), ?, ?)")) {
                                        $bonus->bind_param("sii", $referral_code, $ID, $incentive);
                                        $bonus->execute();
                                        $bonus->close();
                                    } else {
                                        trigger_error($dbcon->error);
                                    }

                                    if($bonus = $dbcon->prepare("UPDATE user SET balance = balance + ?, available = available + ? WHERE referral_code = ?")) {
                                        $bonus->bind_param("iis", $incentive, $incentive, $referral_code);
                                        $bonus->execute();
                                        $bonus->close();
                                    } else {
                                        trigger_error($dbcon->error);
                                    }
                                }

                                if($update = $dbcon->prepare("UPDATE user SET balance = balance + (SELECT SUM(offer + interest) FROM bid WHERE ID = ?), modified = CURRENT_TIMESTAMP() WHERE ID = (SELECT user_id FROM bid WHERE ID = ?)")) {
                                    $update->bind_param("ii", $bid_id, $bid_id);
                                    $update->execute();
    
                                    if($update->affected_rows) {
                                        $alerts[] = "You have successfully confirmed bid.";
                                    } else {
                                        trigger_error($dbcon->error);
                                        $errors[] = "Although you have successfully confirmed the bid, there was an error updating some details. Please contact Support.";
                                    }
                                }
                            } else {
                                trigger_error($dbcon->error);
                                $errors[] = "There was an error checking referral while confirming deposit.";
                            }
                        }
                        $confirm->close();
                    } else {
                        $errors[] = "";
                    }
                }
            } else {
                $errors[] = "There was an error preparing your confirmation.";
            }
        } else {
            if(!is_numeric($_POST['bid']))
            $errors[] = "The bid reference can only be numeric.";
            if(!is_numeric($_POST['offer']))
            $errors[] = "The amount offered can only numeric.";
        }
    } else {
        if(!$_POST['bid']) 
        $errors[] = "There is no bid to confirm selected.";
        if(!$_POST['offer'])
        $errors[] = "There is no offer in the bid you are confirm.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: payments");
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
                    <h1>Payments</h1>
                    <p>Unpaid bids/offers from your withdrawals</p>
                </div>
                <div class="section__content">
                    <div class="deposits">
<?php if($open_bids = $auction->open_bids($user_id)): ?>

                        <div class="container">
<?php foreach($open_bids as $bid): ?>

                            <div class="container__column container__column--half container__column--auction">
                            <form class="form" method="POST">
                                <div class="input">
                                    <input type="hidden" name="bid" value="<?php echo($bid['ID']); ?>">
                                    <div class="input__group">
                                        <label class="form__label">Bidder</label>
                                        <input type="text" name="bidder" class="form__control form__control--readonly" value="<?php echo($bid['bidder']); ?>" readonly>
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Contact Number</label>
                                        <input type="text" name="phonenumber" class="form__control form__control--readonly" value="<?php echo($bid['phonenumber']); ?>" readonly>
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Bank Reference</label>
                                        <input type="text" name="sale" class="form__control" value="<?php echo($bid['deposit_ref']); ?>" readonly>
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Offer</label>
                                        <input type="text" name="offer" class="form__control" value="<?php echo($bid['offer']); ?>" readonly>
                                    </div>
<?php if(isset($bid['pop'])): ?>

                                    <div class="input__group">
                                        <label class="form__label">Proof of Payment</label>
                                        <p><a href="<?php echo(attachment_url($bid['pop'])); ?>" target="_blank">View</a></p>
                                    </div>
<?php endif; ?>

                                </div>
                                <div class="form__group">
                                    <input type="submit" name="confirm" class="button" value="Confirm Deposit">
                                </div>
                            </form>

                            </div>
<?php endforeach; ?>

                        </div>
<?php else: ?>

                        <p>You do not have an pending deposits.</p>
<?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
<?php
get_footer();