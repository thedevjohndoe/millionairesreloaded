<?php
/**
 * Notifications template
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
                    <h1>Notifications</h1>
                </div>
                <div class="section__content">
<?php if($notifications = $profile->get_notifications()): ?>

                    <div class="container">
                        <div class="container__column">
                            <div class="table">
                                <ul class="table__header">
                                    <li class="table__column">ID</li>
                                    <li class="table__column">Received</li>
                                    <li class="table__column">Message</li>
                                </ul>
<?php foreach($notifications as $notification): ?>

                                <ul class="table__row">
                                    <li class="table__column"><label></label><?php echo($notification['ID']); ?></li>
                                    <li class="table__column"><label></label><?php echo($notification['create']); ?></li>
                                    <li class="table__column"><label></label><?php echo($notification['content']); ?></li>
                                </ul>
<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
<?php else: ?>

                    <p>You currently do not have any notifications.</p>
<?php endif; ?>
                </div>
            </div>
        </div>
<?php
get_footer();