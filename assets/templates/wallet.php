<?php
/**
 * Wallet template
 */
$user_id = $_SESSION['user_id'];
$profile = new profile($user_id);
$info    = $profile->get_info();


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $alerts = array();
    $errors = array();

    if($_POST['description'] && $_POST['bank_name'] && $_POST['account_holder'] && $_POST['account_type'] && $_POST['account_number']) {
        $bank_name = $dbcon->real_escape_string($_POST['bank_name']);
        $description = $dbcon->real_escape_string($_POST['description']);
        $account_type = $dbcon->real_escape_string($_POST['account_type']);
        $account_holder = $dbcon->real_escape_string($_POST['account_holder']);
        $account_number = $dbcon->real_escape_string($_POST['account_number']);

        if($query = $dbcon->prepare("INSERT INTO payment (user_id, description, bank_name, account_holder, account_type, account_number) VALUES (?, ?, ?, ?, ?, ?)")) {
            $query->bind_param("isssss", $user_id, $description, $bank_name, $account_holder, $account_type, $account_number);
            $query->execute();
            if($detail_id = $query->insert_id) {
                $alerts[] = "Your payment details were added successfully.";
            } else {
                $errors[] = "There was an error adding your payment details.";
            }
            $query->close();
        } else {
            trigger_error($dbcon->error);
            $errors[] = "There was an error submitting your payment details.";
        }
    } else {
        if(!$_POST['description']) $errors[] = "A description of your payment details is required.";
        if(!$_POST['bank_name']) $errors[] = "Name of banking insititution must must be provided.";
        if(!$_POST['account_holder']) $errors[] = "Account holder details help with verification of payment details.";
        if(!$_POST['account_type']) $errors[] = "It is helpful to provide account type.";
        if(!$_POST['account_number']) $errors[] = "We need to know which account payments must be deposited into.";
    }

    if($alerts) $_SESSION['alerts'] = $alerts;
    if($errors) $_SESSION['errors'] = $errors;

    header("Location: wallet");
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
<?php check_errors_and_alerts() ?>

                <div class="section__header">
                    <h1>Wallet</h1>
                </div>
                <div class="section__content">
                    <div class="container">
                        <div class="container__column container__column--half">
                            <h2>Account Balances</h2>
                            <ul>
                                <li>Coins Balance: <?php echo($info['balance']); ?></li>
                                <li>Coins Available: <?php echo($info['available']); ?></li>
                            </ul>
                        </div>
                        <div class="container__column container__column--half">
                            <h2>New Payment Details</h2>
                            <form class="form" method="POST">
                                <div class="input">
                                    <div class="input__group">
                                        <label class="form__label">Description</label>
                                        <input type="text" class="form__control" name="description" placeholder="Description, e.g. Main Account">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Bank Name</label>
                                        <input type="text" class="form__control" name="bank_name" placeholder="Bank Name, e.g. South African Bank">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Account Holder</label>
                                        <input type="text" class="form__control" name="account_holder" placeholder="Account Holder, e.g. John Doe">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Account Number</label>
                                        <input type="text" class="form__control" name="account_number" placeholder="Account Number, e.g. 0101010101">
                                    </div>
                                    <div class="input__group">
                                        <label class="form__label">Account Type</label>
                                        <input type="text" class="form__control" name="account_type" placeholder="Account Type, e.g. Savings">
                                    </div>
                                </div>
                                <div class="form__group">
                                    <input type="submit" class="button" name="create" value="Submit Payment Details">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="payment__details">
                        <div class="container">
                            <div class="container__column container__column--full">
                                <h2>Payment Details</h2>
<?php if($details = $profile->get_payment_details()): ?>

                                <table class="table">
                                    <tr class="table__header">
                                        <th>Description</th>
                                        <th>Bank</th>
                                        <th>Account Holder</th>
                                        <th>Account Number</th>
                                        <th>Account Type</th>
                                    </tr>
<?php foreach($details as $detail): ?>

                                    <tr class="table__row">
                                        <td><label><?php echo($detail['description']); ?></label></td>
                                        <td><label><?php echo($detail['bank_name']); ?></label></td>
                                        <td><label><?php echo($detail['account_holder']); ?></label></td>
                                        <td><label><?php echo($detail['account_number']); ?></label></td>
                                        <td><label><?php echo($detail['account_type']); ?></label></td>
                                    </tr>
<?php endforeach; ?>

                                </table>
<?php else: ?>

                                <p>You have not yet added payment details.</p>
<?php endif; ?>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
get_footer();