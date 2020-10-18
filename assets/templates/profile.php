<?php
/**
 * User profile template
 */
$user_id = $_SESSION['user_id'];
$profile = new profile($user_id);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_POST['firstname'] && $_POST['surname'] && $_POST['phonenumber']) {
        $firstname = $dbcon->real_escape_string($_POST['firstname']);
        $surname = $dbcon->real_escape_string($_POST['surname']);
        $phonenumber = $dbcon->real_escape_string($_POST['phonenumber']);

        if($update = $dbcon->prepare("UPDATE user SET firstname = ?, surname = ?, phonenumber = ? WHERE ID = ?")) {
            $update->bind_param("sssi", $firstname, $surname, $phonenumber, $user_id);
            $update->execute();
            if($update->affected_rows) {
                if($_POST['password']) {
                    $password = $dbcon->real_escape_string($_POST['password']);
                    $password = md5($password);
                    if($update_password = $dbcon->prepare("UPDATE user SET password  = ? WHERE ID = ?")) {
                        $update_password->bind_param("si", $password, $user_id);
                        $update_password->execute();
                        $update_password->close();
                    }
                }
                $alerts[] = "You changes were saved successfully.";
            } else {
                $errors[] = "I don't know why, but there was an error saving your changes.";
            }
            $update->close();
        } else {
            trigger_error($dbcon->error);
            $errors[] = "There is an unknown error somwehere.";
        }
    } else {
        if(!$_POST['firstname'])
        $errors[] = "Your first name is required.";
        if(!$_POST['surname'])
        $errors[] = "Your surname is required.";
        if(!$_POST['phonenumber'])
        $errors[] = "Phone number cannot be blank.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: profile");
    exit;
}

if(!$info = $profile->get_info()) {
    header("Location: ./");
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
                    <h2>My Profile</h2>
                </div>
                <div class="section__content">
                    <div class="profile">
                        <div class="container">
                            <div class="container__column container__column--full">
                            <form class="form" method="POST">
                                <div class="input">
                                    <div class="input__group">
                                        <label class="form__label">Name</label>
                                        <input type="text" class="form__control" name="firstname" value="<?php echo($info['firstname']); ?>" placeholder="First Name">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Surname</label>
                                        <input type="text" class="form__control" name="surname" value="<?php echo($info['surname']); ?>" placeholder="Last Name">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Email</label>
                                        <input type="email" class="form__control" name="email_address" value="<?php echo($info['email_address']); ?>" readonly>
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Mobile</label>
                                        <input type="text" class="form__control" name="phonenumber" value="<?php echo($info['phonenumber']); ?>" placeholder="Mobile Phone Number">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Username</label>
                                        <input type="text" class="form__control" name="username" value="<?php echo($info['username']); ?>" readonly>
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Password (Leave blank if not changing)</label>
                                        <input type="password" class="form__control" name="password" value="" autocomplete="off">
                                    </div>
                                </div>
                                <div class="form__group">
                                    <input type="submit" class="button button--login" value="Update Profile">
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
get_footer();
