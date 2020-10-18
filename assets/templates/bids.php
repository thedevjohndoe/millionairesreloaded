<?php
/**
 * Bids template
 */
$user_id = $_SESSION['user_id'];
$profile = new profile($user_id);

get_header();
?>

        <div class="content__container">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="content__section">
                <div class="section__header">
                    <h1>Bids</h1>
                </div>
                <div class="section__content">
<?php if($bids = $profile->get_bids()): ?>
                    <div class="container">
<?php foreach($bids as $bid): ?>

                        <div class="container__column containter__column--bid container__column--half">
                            <ul>
                                <li><label>Date: <?php echo($bid['short_date']); ?></label></li>
                                <li><label>Seller Contact: <?php echo($bid['phonenumber']); ?></label></li>
                                <li><label>Offer: <?php echo($bid['offer']); ?> Coins</label></li>
                                <li class="table__column"><label>Banking Details: <?php echo("$bid[bank_name] / $bid[account_number]"); ?></label></li>
                                <li class="table__column"><label>Payment Ref: <?php echo($bid['bid_ref']); ?></label></li>
                                <li><label>Status: <?php echo(($bid['confirmed']) ? "Paid" : "Unpaid"); ?></label></li>
                                <li>Maturity Status: <?php echo(($bid['mature']) ? "Mature" : "Pending"); ?></li>
                                <li>Maturity Date: <?php echo(($bid['confirmed']) ? $bid['maturity_date'] : "Unpaid"); ?></li>
                            </ul>
                        </div>
<?php endforeach; ?>

                    </div>
<?php else: ?>

                    <p>You do not have a bidding history.</p>
<?php endif; ?>

                </div>
            </div>
        </div>
<?php
get_footer();
