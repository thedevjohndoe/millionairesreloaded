<?php
/**
 * Auction template
 */
$user_id = $_SESSION['user_id'];
$auction = new auction();
$listing = false;

$current = date("H:i");
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
    $listing = true;
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $alerts = array();
        $errors = array();
    
        if($_POST['sale'] && $_POST['term'] && $_POST['offer']) {
            $sale_id = (int)$_POST['sale'];
            $term_id = (int)$_POST['term'];
    
            if(is_numeric($_POST['offer']) && $_POST['offer'] >= 200) {
                $offer = $_POST['offer'];

                if($check_bids = $dbcon->prepare("SELECT COUNT(*) FROM bid WHERE deleted = 0 AND confirmed = 0 AND user_id = ?")) {
                    $check_bids->bind_param("i", $user_id);
                    $check_bids->execute();
                    $check_bids->bind_result($bids);
                    $check_bids->fetch();
                    $check_bids->close();

                    if($bids >= 2) {
                        $errors[] = "You cannot place more than two bids per auction.";
                    } else {
                        if(!$bids && $offer > 1000) {
                            $errors[] = "Your first bid cannot exceed 1000 coins. Get at least one 1000 coins bid confirmed first.";
                        } else {
                            if($sale_available = $dbcon->prepare("SELECT available, user_id, (SELECT COUNT(*) FROM relationship WHERE (friend_a = ? AND friend_b = sale.user_id) OR (friend_a = sale.user_id AND friend_b = ?)) AS relationship FROM sale WHERE ID = ?")) {
                                $sale_available->bind_param("iii", $user_id, $user_id, $sale_id);
                                $sale_available->execute();
                                $sale_available->bind_result($available, $seller_id, $relationship);
                                $sale_available->fetch();
                                $sale_available->close();
                
                                if($offer <= $available && $seller_id != $user_id && !$relationship) {
                                    if($update = $dbcon->prepare("UPDATE sale SET sold = sold + ?, available = available - ? WHERE ID = ?")) {
                                        $update->bind_param("iii", $offer, $offer, $sale_id);
                                        $update->execute();
                                        if($update->affected_rows) {
                                            if($bid = $dbcon->prepare("INSERT INTO bid (user_id, sale_id, term_id, offer, interest) VALUES (?, ?, ?, ?, ((? / 100) * (SELECT interest_rate FROM term WHERE ID = ?)))")) {
                                                $bid->bind_param("iiiiii", $user_id, $sale_id, $term_id, $offer, $offer, $term_id);
                                                $bid->execute();
                                                if($bid_id = $bid->insert_id) {
                                                    $alerts[] = "Your bid was submitted successfully.";
                                                } else {
                                                    trigger_error($dbcon->error);
                                                    $errors[] = "We have encountered an error submitting your offer.";
                                                }
                                                $bid->close();
                                            } else {
                                                trigger_error($dbcon->error);
                                                $errors[] = "Bid could not be submitted. Please try again.";
                                            }
                                        } else {
                                            trigger_error($dbcon->error);
                                            $errors[] = "Bid could not be processed at this time.  Sale: $sale_id, Offer: $offer";
                                        }
                                        $update->close();
                                    } else {
                                        $errors[] = "There was an error processing bid.";
                                    }
                
                                    
                                } else {
                                    if($offer > $available)
                                    $errors[] = "There are only $available coins available in this sale. You cannot offer more than that.";
                                    if($seller_id === $user_id)
                                    $errors[] = "You cannot bid on your own withdrawal.";
                                    if($relationship)
                                    $errors[] = "You cannot bid on a withdrawals of users you are tagged with.";
                                }
                            } else {
                                $errors[] = "There was an error matching your offer and available coins.";
                            }
                        }
                    }
                } else {
                    $errors[] = "There was an error verifying bid history.";
                }
            } else {
                if(!is_numeric($_POST['offer']))
                $errors[] = "Your offer needs to be in numbers only. No text allowed.";
                if(is_numeric($_POST['offer']) && $_POST['offer'] < 200)
                $errors[] = "You offer cannot be below 200.";
            }
        } else {
            if(!$_POST['sale'])
            $errors[] = "There is no sale selected for your offer.";
            if(!$_POST['term'])
            $errors[] = "An investment term must be selected.";
            if(!$_POST['offer'])
            $errors[] = "You have not provided an amount for your offer.";
        }
    
        if($alerts) $_SESSION['alerts'] = $alerts;
        if($errors) $_SESSION['errors'] = $errors;
    
        header("Location: auction");
        exit;
    }
} else {
    $listing = false;
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
                    <h1>Coin Auction</h1>
                </div>
                <div class="section__content">
<?php if($listing && $open_sales = $auction->open_sales($user_id)): ?>

                    <div class="container">

<?php
foreach($open_sales as $sale):
    if($sale['seller_id'] == $user_id) {
        $class = "container__column--quarter container__column--auction container__column--auction-self";
    } else {
        if($sale['relationship']) {
            $class = "container__column--quarter container__column--auction container__column--auction-related";
        } else {
            $class = "container__column--quarter container__column--auction";
        }
    }
?>
                        <div class="container__column <?php echo($class); ?>">
                            <!-- <h3>Lot #<?php echo("$sale[ID] ($sale[bank_name])"); ?></h3> -->
                            <h3>Open Lot</h3>
                            <form class="form" method="POST">
                                <div class="input">
                                    <input type="hidden" name="sale" value="<?php echo($sale['ID']); ?>">
                                    <!-- <div class="input__group">
                                        <label class="form__label">Sale Reference</label>
                                        <input type="text" class="form__control" name="sale_ref" value="<?php echo($sale['reference']); ?>" readonly>
                                    </div> -->
                                    <!-- <div class="input__group">
                                        <label class="form__label">Coins Allocated</label>
                                        <input type="text" class="form__control" name="sale" value="<?php echo($sale['allocated']); ?>" readonly>
                                    </div> -->
                                    <div class="input__group">
                                        <label class="form__label">Coins Available</label>
                                        <input type="text" class="form__control" name="sale_coins" value="<?php echo($sale['available']); ?>" readonly>
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Bid Offer</label>
                                        <input type="text" class="form__control" name="offer" placeholder="Coins to buy">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Investment Period</label>
                                        <select type="text" class="form__control" name="term" placeholder="Coins to buy">
                                            <option value>No Selection</option>
<?php
if($terms = $auction->get_terms()):
foreach($terms as $term):
?>

                                            <option value="<?php echo($term['ID']); ?>"><?php echo("$term[duration] days ($term[interest_rate]% interest rate)"); ?></option>
<?php
endforeach;
endif;
?>

                                        </select>
                                    </div>
                                </div>
                                <div class="form__group">
                                    <input type="submit" class="button button--auction" value="Submit Bid">
                                </div>
                            </form>
                        </div>
<?php endforeach; ?>

                    </div>
<?php else: ?>

                    <p>The auction is currently closed. Auction times are: 06:00, 12:00 and 18:00 every day.</p>
                    <p><span id="auction"></span></p>
<?php endif; ?>
                </div>
            </div>
        </div>
<?php
get_footer();