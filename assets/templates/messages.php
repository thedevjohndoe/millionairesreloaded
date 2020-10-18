<?php
/**
 * Wallet template
 */
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$profile = new profile($user_id);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_POST['description'] && $_POST['target']) {
        $description = $dbcon->real_escape_string($_POST['description']);
        if(is_numeric($_POST['target'])) {
            $target = $_POST['target'];
            if($goal = $dbcon->prepare("INSERT INTO goal (user_id, description, target) VALUES (?, ?, ?)")) {
                $goal->bind_param("isi", $user_id, $description, $target);
                $goal->execute();
                if($goal_id = $goal->insert_id) {
                    $alerts[] = "Your goal was set successfully.";
                } else {
                    $errors[] = "There was an error setting your goal.";
                }
            } else {
                trigger_error($dbcon->error);
                $errors[] = "There was an error preparing your goal.";
            }
        } else {
            $errors[] = "Target can only be a numeric value.";
        }
    } else {
        if(!$_POST['description'])
        $errors[] = "You need to describe your goal.";
        if(!$_POST['target'])
        $errors[] = "A goal cannot be set without a target.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: goal");
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
                    <h1>Messages</h1>
                </div>
                <div class="section__content">
<?php if($messages = $profile->get_messages()): ?>
                    <div class="container">
<?php foreach($messages as $message): ?>
                        <div class="container__column">
                            <p><?php echo("$message[created]: $message[content]"); ?></p>
                        </div>
<?php endforeach; ?>
                    </div>
<?php else: ?>
                    <p>You do not have any messages.</p>
<?php endif; ?>
                </div>
            </div>
        </div>
<?php
get_footer();