<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "vendor/autoload.php";
require_once "configuration.php";

$mail = new PHPMailer(true);
$mail->isHTML(true);
$mail->From = "no-reply@millionairesreloaded.co.za";
$mail->FromName = SITE_NAME;

function login_header() {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/scss/index.css?v=<?php echo(time()); ?>">
    <title><?php echo(SITE_NAME); ?></title>
</head>
<body>
<div class="login">
<h1><a href="./"><?php echo(SITE_NAME); ?></a></h1>
<?php
/** Check for alerts and errors */
check_errors_and_alerts();
}

function login_footer() {
?>
</div>
</body>
</html>
<?php
}

$action = isset($_GET['action']) ? $_GET['action'] : "login";

switch($action) {
case "register":
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_POST['firstname'] && $_POST['surname'] && $_POST['email_address'] && $_POST['phonenumber'] && $_POST['username'] && $_POST['password']) {
        if(!preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $_POST['username']) && !preg_match('/\s/', $_POST['username']) && strlen($_POST['username']) >= 5 && strlen($_POST['username']) <= 15) {
            $firstname = $dbcon->real_escape_string($_POST['firstname']);
            $surname = $dbcon->real_escape_string($_POST['surname']);
            $referrer_id = 0;
            $referral_code = referral_code();
            $verification_key = verification_key();
            $email_address = $dbcon->real_escape_string($_POST['email_address']);
            $phonenumber = $dbcon->real_escape_string($_POST['phonenumber']);
            $username = $dbcon->real_escape_string($_POST['username']);
            $password = $dbcon->real_escape_string($_POST['password']);
            $password = md5($password);

            if($_POST['ref_code']) {
                $ref_code = $dbcon->real_escape_string($_POST['ref_code']);
                if($get_referrer = $dbcon->prepare("SELECT ID FROM user WHERE referral_code = ?")) {
                    $get_referrer->bind_param("s", $ref_code);
                    $get_referrer->execute();
                    $get_referrer->bind_result($referrer_id);
                    $get_referrer->fetch();
                    $get_referrer->close();
                } else {
                    trigger_error($dbcon->error);
                }
            }

            if($email = $dbcon->prepare("SELECT ID FROM user WHERE email_address = ?")) {
                $email->bind_param("s", $email_address);
                $email->execute();
                $result = $email->get_result();

                if($result && $result->num_rows > 0) {
                    $errors[] = "The email address provided is already in use.";
                } else {
                    if($availability = $dbcon->prepare("SELECT ID FROM user WHERE username = ?")) {
                        $availability->bind_param("s", $username);
                        $availability->execute();
                        $result = $availability->get_result();

                        if($result && $result->num_rows > 0) {
                            $errors[] = "Username already in use.";
                        } else {
                            if($register = $dbcon->prepare("INSERT INTO user (firstname, surname, referral_code, email_address, phonenumber, username, password, verification_key) VALUE (?, ?, ?, ?, ?, ?, ?, ?)")) {
                                $register->bind_param("ssssssss", $firstname, $surname, $referral_code, $email_address, $phonenumber, $username, $password, $verification_key);
                                $register->execute();
                    
                                if($user_id = $register->insert_id) {
                                    $origin = get_user_origin();
                                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                                    $description = "Created a new account.";
                                    if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES (?, ?, ?, ?)")) {
                                        $note->bind_param("isss", $user_id, $description, $origin, $user_agent);
                                        $note->execute();
                                        $note->close();
                                    }

                                    if($_POST['ref_code']) {
                                        if($lookup = $dbcon->prepare("INSERT INTO referral (referrer_id, referred_id, referral_code) VALUES (?,?, ?)")) {
                                            $lookup->bind_param("iis", $referrer_id, $user_id, $ref_code);
                                            $lookup->execute();
                                            $lookup->close();
                                        }
                                    }

                                    // Send verification email
                                    $verification_address = generate_verification_address($verification_key);
                                    $mail->addAddress($email_address, $firstname);
                                    $mail->Subject = "Verify Your Email Address";
                                    $mail->Body = "
                                    <p>Hey $firstname,</p>
                                    <p>Thank you for signing up with Global Goal Diggers&dash;your number 1 coin mining platform.</p>
                                    <p>To complete your registration, click on the link below to verify your account:</p>
                                    <p><a href=\"$verification_address\">$verification_address</a></p>
                                    <p>You can copy and copy the link onto your browser's address bar.</p>
                                    <p>Regards,<br/>
                                    The Global Goal Diggers Team</p>";

                                    try {
                                        $mail->send();
                                    } catch (Exception $e) {
                                        trigger_error($mail->ErrorInfo);
                                    }

                                    $alerts[] = "Your account was created successfully and a verification email has been sent.";
                                } else {
                                    if(TROUBLESHOOTING) trigger_error($dbcon->error);
                                    $errors[] = "There was an error creating your account.";
                                }
                            } else {
                                if(TROUBLESHOOTING) trigger_error($dbcon->error);
                                $errors[] = "There was an error registering your account.";
                            }
                        }

                        $availability->close();
                    } else {
                        $errors[] = "There was an error preparing your registration.";
                        if(TROUBLESHOOTING) trigger_error($dbcon->error);
                    }
                }

                $email->close();
            } else {
                if(TROUBLESHOOTING) trigger_error($dbcon->error);
            }
        } else {
            if(preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $_POST['username'])) {
                $errors[] = "Special characters are not allowed in usernames. You can only use an underscore ( _ ).";
            } elseif(preg_match('/\s/', $_POST['username'])) {
                $errors[] = "Spaces are not allowed in usernames.";
            } elseif(strlen($_POST['username']) < 5 || strlen($_POST['username']) > 15) {
                $errors[] = "Your username needs to be between 5 and 15 characters long.";
            }
        }
    } else {
        if(!$_POST['firstname']) $errors[] = "We need to know your first name.";
        if(!$_POST['surname']) $errors[] = "We need to know your last name.";
        if(!$_POST['email_address']) $errors[] = "An email address is required";
        if(!$_POST['phonenumber']) $errors[] = "An phone number is required";
        if(!$_POST['username']) $errors[] = "Username cannot be blank.";
        if(!$_POST['password']) $errors[] = "Password cannot be blank";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: login.php?action=register");
    exit;
}
login_header();
?>
<p>Use the form below to create a new account.</p>
<form class="form" method="POST">
    <div class="input">
        <div class="input__group">
            <label class="form__label">Name</label>
            <input type="text" class="form__control" name="firstname" placeholder="First Name">
        </div>
        <div class="input__group">
            <label class="form__label">Surname</label>
            <input type="text" class="form__control" name="surname" placeholder="Last Name">
        </div>
        <div class="input__group">
            <label class="form__label">Email</label>
            <input type="email" class="form__control" name="email_address" placeholder="Email Address">
        </div>
        <div class="input__group">
            <label class="form__label">Mobile</label>
            <input type="text" class="form__control" name="phonenumber" placeholder="Mobile Phone Number">
        </div>
        <div class="input__group">
            <label class="form__label">Username</label>
            <input type="text" class="form__control" name="username" placeholder="Username">
        </div>
        <div class="input__group">
            <label class="form__label">Password</label>
            <input type="password" class="form__control" name="password" placeholder="Account Password">
        </div>
        <div class="input__group">
            <label class="form__label">Referral Code</label>
            <input type="text" class="form__control" name="ref_code" placeholder="Referral Code">
        </div>
    </div>
    <div class="form__group">
        <input type="submit" class="button button--login" value="Register">
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</form>
<?php
login_footer();
break;
case "verify":
if(isset($_GET['key'])) {
    $alerts = array();
    $errors = array();

    $key = $dbcon->real_escape_string($_GET['key']);

    if($confirm = $dbcon->prepare("SELECT ID, verified FROM user WHERE deleted = 0 AND disabled = 0 AND verification_key  = ?")) {
        $confirm->bind_param("s", $key);
        $confirm->execute();
        $confirm->bind_result($ID, $verified);
        $confirm->fetch();
        $confirm->close();

        if($ID) {
            if($verified) {
                $errors[] = "This email verification is no longer active.";
            } else {
                if($verify = $dbcon->prepare("UPDATE user SET verified = 1 WHERE ID = ?")) {
                    $verify->bind_param("i", $ID);
                    $verify->execute();
    
                    if($verify->affected_rows) {
                        $origin = get_user_origin();
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                        $description = "Verified details.";
                        if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES (?, ?, ?, ?)")) {
                            $note->bind_param("isss", $ID, $description, $origin, $user_agent);
                            $note->execute();
                            $note->close();
                        }

                        $alerts[] = "Your details were verified successfully.";
                    }
    
                    $verify->close();
                }
            }
        } else {
            $errors[] = "This email verification is not available.";
        }
    } else {
        $errors[] = "There was an error preparing your verification.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    // header("Location: login.php");
    // exit;
} else {
    header("Location: login.php");
    exit;
}
login_header();
?>
<p>Log into <a href="login.php"><?php echo(SITE_NAME); ?></a>.</p>
<?php
login_footer();
break;
case "resend-verification":
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_POST['email_address']) {
        $email_address = $dbcon->real_escape_string($_POST['email_address']);
        if($retrieve = $dbcon->prepare("SELECT firstname, verification_key FROM user WHERE deleted = 0 AND disabled = 0 AND email_address = ? LIMIT 1")) {
            $retrieve->bind_param("s", $email_address);
            $retrieve->execute();
            $retrieve->bind_result($firstname, $verification_key);
            $retrieve->fetch();
            $retrieve->close();

            if($verification_key) {
                $origin = get_user_origin();
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $description = "Resent verification email.";
                if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES ((SELECT ID FROM user WHERE email_address = ?), ?, ?, ?)")) {
                    $note->bind_param("ssss", $email_address, $description, $origin, $user_agent);
                    $note->execute();
                    $note->close();
                }

                $verification_address = generate_verification_address($verification_key);
                $mail->addAddress($email_address, $firstname);
                $mail->Subject = "Verify Your Email Address";
                $mail->Body = "
                <p>Hey $firstname,</p>
                <p>Thank you for signing up with Global Goal Diggers&dash;your number 1 coin mining platform.</p>
                <p>To complete your registration, click on the link below to verify your account:</p>
                <p><a href=\"$verification_address\">$verification_address</a></p>
                <p>You can copy and paste the link onto your browser's address bar.</p>
                <p>Regards,<br/>
                The Global Goal Diggers Team</p>";

                try {
                    $mail->send();
                } catch (Exception $e) {
                    trigger_error($mail->ErrorInfo);
                }
            }

            $alerts[] = "If there is an active account using the email address provided, a verification link will be sent shortly.";
        }
    } else {
        if(!$_POST['email_address'])
        $errors[] = "You need to provide and email address to verify.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: login.php?action=resend-verification");
    exit;
}
login_header();
?>
<p>Resend email verification.</p>
<form class="form" method="POST">
    <div class="input">
        <div class="input__group">
            <label class="form__label">Email</label>
            <input type="text" class="form__control" name="email_address" placeholder="Email Address">
        </div>
    </div>
    <div class="form__group">
        <input type="submit" class="button button--login" value="Send">
        <p>Already verified? <a href="login.php">Login</a></p>
    </div>
</form>
<?php
login_footer();
break;
case "reset-password":
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_GET['reset_token']) {
        $reset_token = $_GET['reset_token'];
        if($_POST['new_password'] && $_POST['confirm_password']) {
            if($_POST['new_password'] == $_POST['confirm_password']) {
                if(strlen($_POST['new_password']) >= 8 || strlen($_POST['new_password']) <= 12) {
                    $password = $dbcon->real_escape_string($_POST['new_password']);
                    $password = md5($password);
                    if($update = $dbcon->prepare("UPDATE user SET password = ? WHERE reset_token = ?")) {
                        $update->bind_param("ss", $password, $reset_token);
                        $update->execute();
                        if($update->affected_rows) {
                            $origin = get_user_origin();
                            $user_agent = $_SERVER['HTTP_USER_AGENT'];
                            $description = "Reset account password.";
                            if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES (?, ?, ?, ?)")) {
                                $note->bind_param("isss", $user_id, $description, $origin, $user_agent);
                                $note->execute();
                                $note->close();
                            }

                            $_SESSION['alerts'] = array("Your password was updated successfully.");
                            header("Location: login.php");
                            exit;
                        } else {
                            $errors[] = "There was an error updating your password.";
                        }
                        $update->close();
                    }
                } else {
                    $errors[] = "Your password needs to be between 8 and 12 characters long.";
                }
            } else {
                $errors[] = "Your passwords do not match.";
            }
        } else {
            if(!$_POST['new_password'])
            $errors[] = "A new password is required for a password reset.";
            elseif(!$_POST['confirm_password'])
            $errors[] = "You need to confirm new password.";
        }
    } else {
        $_SESSION['errors'] = array("You cannot reset a password without a reset token.");
        header("Location: login.php?action=reset-password");
        exit;
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: $_SERVER[REQUEST_URI]");
    exit;
}
login_header();
?>
<p>New password.</p>
<form class="form" method="POST">
    <div class="input">
        <div class="input__group">
            <label class="form__label">New Password</label>
            <input type="password" class="form__control" name="new_password" placeholder="New Account Password">
        </div>
    </div>
    <div class="input">
        <div class="input__group">
            <label class="form__label">Confirm Password</label>
            <input type="password" class="form__control" name="confirm_password" placeholder="Confirm New Password">
        </div>
    </div>
    <div class="form__group">
        <input type="submit" class="button button--login" value="Search">
        <p>Do not have an account? <a href="login.php?action=register">Register</a></p>
    </div>
</form>
<?php
login_footer();
break;
case "forgot-password":
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if($_POST['email_address']) {
        $reset_token = generate_reset_token();
        $reset_address = generate_reset_address($reset_token);
        $email_address = $dbcon->real_escape_string($_POST['email_address']);
        if($update = $dbcon->prepare("UPDATE user SET reset_token = ? WHERE deleted = 0 AND disabled = 0 AND email_address = ?")) {
            $update->bind_param("ss", $reset_token, $email_address);
            $update->execute();
            if($update->affected_rows) {
                $origin = get_user_origin();
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $description = "Initiated password reset.";
                if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES (?, ?, ?, ?)")) {
                    $note->bind_param("isss", $user_id, $description, $origin, $user_agent);
                    $note->execute();
                    $note->close();
                }

                if($details = $dbcon->prepare("SELECT firstname FROM user WHERE email_address = ?")) {
                    $details->bind_param("s", $email_address);
                    $details->execute();
                    $details->bind_result($firstname);
                    $details->fetch();
                    $details->close();

                    $mail->addAddress($email_address, $firstname);
                    $mail->Subject = "Password Reset";
                    $mail->Body = "
                    <h3>Dear $firstname,</h3>
                    <p>We have received a request to reset your password.</p>
                    <p>Go to <a href=\"$reset_address\">$reset_address</a> to reset your password.</p>
                    <p>Regards,<br/>
                    The Global Goal Diggers Team</p>";

                    try {
                        $mail->send();
                    } catch (Exception $e) {
                        trigger_error($mail->ErrorInfo);
                    }
                } else {
                    $errors[] = "Something did not happen here.";
                    trigger_error($dbcon->error);
                }
                $alerts[] = "A password reset link has been sent to your email address.";
            } else {
                $errors[] = "There is no active account associated with the email address provided.";
            }
            $update->close();
        } else {
            $errors[] = "There was an error preparing reset token.";
        }
    } else {
        if(!$_POST['email_address'])
        $errors[] = "An email address is required in order to recover an account.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: login.php?action=forgot-password");
    exit;
}
login_header();
?>
<p>Recover your account.</p>
<form class="form" method="POST">
    <div class="input">
        <div class="input__group">
            <label class="form__label">Email</label>
            <input type="text" class="form__control" name="email_address" placeholder="Email Address">
        </div>
    </div>
    <div class="form__group">
        <input type="submit" class="button button--login" value="Search">
        <p>Do not have an account? <a href="login.php?action=register">Register</a></p>
    </div>
</form>
<?php
login_footer();
break;
case "logout":
    if(isset($_SESSION['user_id'])) {
        $ID     = $_SESSION['user_id'];
        $errors = array();
        if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES (?, ?, ?, ?)")) {
            $origin = get_user_origin();
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $description = "Logged out of account.";
            $note->bind_param("isss", $ID, $description, $origin, $user_agent);
            $note->execute();
            $note->close();

            session_unset();
            setcookie(session_name(), '', 100);
            session_destroy();
            $_SESSION = array();

            header("Location: login.php");
	        exit();
        } else {
            if(TROUBLESHOOTING) trigger_error($dbcon->error);
        }
    } else {
        header("Location: dashboard.php");
        exit;
    }
break;
case "login":
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alerts = array();
    $errors = array();

    if(isset($_POST['username']) && isset($_POST['password'])) {
        /**
         * Credentials
         */
        $username = $dbcon->real_escape_string($_POST['username']);
        $password = $dbcon->real_escape_string($_POST['password']);
        $password = md5($password);

        if($query = $dbcon->prepare("SELECT * FROM user WHERE user.username = ? AND user.password = ? LIMIT 1")) {
            $query->bind_param("ss", $username, $password);
            $query->execute();
            $result = $query->get_result();
            if($result && $result->num_rows > 0) {
                $user    = $result->fetch_assoc();
                $user_id = $user['ID'];

                if($user['disabled']) {
                    $errors[] = "Your account has been disabled. Please contact Support.";
                } elseif($user['deleted']) {
                    $errors[] = "The account you are trying to log into has been deleted.";
                } elseif(!$user['verified']) {
                    $errors[] = 'Your account is not verified. <a href="login.php?action=resend-verification">Resend verification email.</a>';
                } else {
                    $origin  = get_user_origin();
                    $comment = "Activity from the same IP: $origin";

                    if($check_origin = $dbcon->prepare("SELECT user_id FROM note WHERE NOT user_id = ? AND origin = ? AND TIMESTAMPDIFF(HOUR, created, CURRENT_TIMESTAMP) < 1")) {
                        $check_origin->bind_param("is", $user_id, $origin);
                        $check_origin->execute();
                        if($result = $check_origin->get_result()) {
                            while($friend = $result->fetch_assoc()) {
                                $friend_id = $friend['user_id'];
                                if($relationship = $dbcon->prepare("INSERT INTO relationship (friend_a, friend_b, comment) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE modified = CURRENT_TIMESTAMP()")) {
                                    $relationship->bind_param("iis", $user_id, $friend_id, $comment);
                                    $relationship->execute();
                                    $relationship->close();
                                }
                            }
                        }
                        $check_origin->close();
                    }

                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $description = "Logged into account.";
                    if($note = $dbcon->prepare("INSERT INTO note (user_id, description, origin, user_agent) VALUES (?, ?, ?, ?)")) {
                        $note->bind_param("isss", $user_id, $description, $origin, $user_agent);
                        $note->execute();
                        $note->close();
                    }

                    session_unset();
                    $_SESSION = array();
                    $_SESSION['user_id'] = $user['ID'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    $_SESSION['user_firstname'] = $user['firstname'];
                    $_SESSION['user_username'] = $user['username'];
                    $_SESSION['referral_code'] = $user['referral_code'];
                    $_SESSION['AUTHORISED'] = true;
                    
                    header("Location: ./");
                    exit;
                }
            } else {
                if(TROUBLESHOOTING) trigger_error($dbcon->error);
                $errors[] = "Username and password do not match.";
            }

            $query->close();
        } else {
            if(TROUBLESHOOTING) trigger_error($dbcon->error);
            $errors[] = "There was an error logging you in.";
        }
    } else {
        if(!isset($_POST['username'])) $errors[] = "Username is required.";
        if(!isset($_POST['password'])) $errors[] = "Password is required.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    // Reload login page
    header("Location: login.php");
    exit;
}
login_header();
?>
<p>Enter username and password below to log in.</p>
<form class="form" method="POST">
    <div class="input">
        <div class="input__group">
            <label class="form__label">Username</label>
            <input type="text" class="form__control" name="username" placeholder="Username">
        </div>
        <div class="input__group">
            <label class="form__label">Password</label>
            <input type="password" class="form__control" name="password" placeholder="Account Password">
        </div>
    </div>
    <div class="form__group">
        <input type="submit" class="button button--login" value="Sign in">
        <p>Do not have an account? <a href="login.php?action=register">Register</a></p>
        <p><a href="login.php?action=forgot-password">Forgot password?</a></p>
    </div>
</form>
<?php
login_footer();
break;
}
