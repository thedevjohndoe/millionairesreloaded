<?php
/**
 * Template home page
 */
$user_id = $_SESSION['user_id'];
$profile = new profile($user_id);
$info   = $profile->get_info();
$goal   = $profile->get_goal();
$target = ($goal) ? $goal['target'] : 0;
get_header();
?>

        <div class="content__container">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="content__section">
                <div class="section__header section__header--dashboard">
                    <h1>Hello <?php echo($_SESSION['user_firstname']); ?>! Welcome to your <?php echo(SITE_NAME); ?> Dashboard.</h1>
                    <p>Join us on whatsapp.<br/>
                    Click here: <a href="https://chat.whatsapp.com/LrhMwx1Zd156Pwmg1N9FPc">https://chat.whatsapp.com/LrhMwx1Zd156Pwmg1N9FPc</a></p>
                </div>
                <div class="section__content">
                    <div class="auction-times">
                        <div class="container">
                            <div class="container__column container__column--full">
                                <h3>AUCTION TIME: 18:00, EVERY DAY</h3>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-container">
                        <div class="container">
                            <div class="container__column container__column--quarter container__column--dashboard">
                                <div class="column__header">
                                    <label>Balance:</label>
                                </div>
                                <div class="column__content">
                                    <label><?php echo($money_format->formatCurrency($info['balance'], "ZAR")); ?></label>
                                </div>
                            </div>
                            <div class="container__column container__column--quarter container__column--dashboard">
                                <div class="column__header">
                                    <label>Paid:</label>
                                </div>
                                <div class="column__content">
                                    <label><?php echo($money_format->formatCurrency($info['paid'], "ZAR")); ?></label>
                                </div>
                            </div>
                            <div class="container__column container__column--quarter container__column--dashboard">
                                <div class="column__header">
                                    <label>Interest:</label>
                                </div>
                                <div class="column__content">
                                    <label><?php echo($money_format->formatCurrency($info['interest'], "ZAR")); ?></label>
                                </div>
                            </div>
                            <div class="container__column container__column--quarter container__column--dashboard">
                                <div class="column__header">
                                    <label>Bonus:</label>
                                </div>
                                <div class="column__content">
                                    <!-- <label><?php echo($money_format->formatCurrency($target, "ZAR")); ?></label> -->
                                    <label>R0,00</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="referral-container">
                        <div class="container">
                            <div class="container__column container__column--full">
                                <p>Your referral code: <?php echo($_SESSION['referral_code']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
<?php
get_footer();
