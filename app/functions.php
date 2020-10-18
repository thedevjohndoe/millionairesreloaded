<?php
function check_errors_and_alerts() {
if(isset($_SESSION['errors'])):
?>
<div class="alert">
    <ul>
<?php
foreach($_SESSION['errors'] as $error) {
    echo("<li>$error</li>");
}
?>
    </ul>
</div>
<?php
unset($_SESSION['errors']);
elseif(isset($_SESSION['alerts'])):
?>
<div class="alert">
    <ul>
<?php
foreach($_SESSION['alerts'] as $alert) {
    echo("<li>$alert</li>");
}
?>
    </ul>
</div>
<?php
unset($_SESSION['alerts']);
endif;
}

function get_user_origin() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $origin = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $origin = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        $origin = $_SERVER["REMOTE_ADDR"];
    }

    return $origin;
}

function referral_code() {
    $length = 5;
    $data = "1234567890abcdefghijklmnopqrstuvwxyz";
    $value = substr(str_shuffle($data), 0, $length);
    return $value;
}

function verification_key() {
    $length = 65;
    $data = "1234567890abcdefghijklmnopqrstuvwxyz";
    $value = substr(str_shuffle($data), 0, $length);
    return $value;
}

function generate_reset_token() {
    $length = 65;
    $data = "1234567890abcdefghijklmnopqrstuvwxyz";
    $value = substr(str_shuffle($data), 0, $length);
    return $value;
}

function generate_reset_address($token = NULL) {
    if(!$token) return;
    $address = "https://millionairesreloaded.co.za/login.php?action=reset-password&reset_token=$token";
    return $address;
}

function generate_verification_address($key = NULL) {
    if(!$key) return;
    $address = "https://millionairesreloaded.co.za/login.php?action=verify&key=$key";
    return $address;
}
