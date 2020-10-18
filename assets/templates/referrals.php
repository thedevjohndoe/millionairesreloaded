<?php
/**
 * Referrals template
 */
$user_id  = $_SESSION['user_id'];
$profile  = new profile($user_id);
$referred = $profile->get_referred();

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
                    <h1>Referrals</h1>
                </div>
                <div class="section__content">
<?php if($referred = $profile->get_referred()): ?>

                    <div class="container">
                        <div class="container__column">
                            <div class="table">
                                <ul class="table__header">
                                    <li class="table__column">Name</li>
                                    <li class="table__column">Surname</li>
                                    <li class="table__column">Username</li>
                                </ul>
<?php foreach($referred as $friend): ?>

                                <ul class="table__row">
                                    <li class="table__column"><label></label><?php echo($friend['firstname']); ?></li>
                                    <li class="table__column"><label></label><?php echo($friend['surname']); ?></li>
                                    <li class="table__column"><label></label><?php echo($friend['username']); ?></li>
                                </ul>
<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
<?php else: ?>

                    <p>You do not have any referred users.</p>
<?php endif; ?>
                </div>
            </div>
        </div>
<?php
get_footer();