<?php
class profile {
    protected $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
    }

    public function get_info() {
        global $dbcon;
        $info = array();

        if($query = $dbcon->prepare("SELECT ID, firstname, surname, email_address, username, phonenumber, balance, available, (SELECT SUM(bid.offer) FROM bid WHERE bid.confirmed = 1 AND bid.user_id = user.ID) AS paid, (SELECT SUM(bid.interest) FROM bid WHERE bid.confirmed = 1 AND bid.user_id = user.ID) AS interest FROM user WHERE ID = ? LIMIT 1")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();

            if($result = $query->get_result()) {
                $info = $result->fetch_assoc();
            }
            
            $query->close();
        } else {
            trigger_error($dbcon->error);
        }

        return $info;
    }

    public function get_goal() {
        global $dbcon;
        $goal = array();

        if($query = $dbcon->prepare("SELECT * FROM goal WHERE user_id = ? ")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();
            
            if($result = $query->get_result()) {
                $goal = $result->fetch_assoc();
            }
            
            $query->close();
        }

        return $goal;
    }

    public function get_payment_details() {
        global $dbcon;
        $details = array();

        if($query = $dbcon->prepare("SELECT * FROM payment WHERE user_id = ?")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();

            if($result = $query->get_result()) {
                while($detail = $result->fetch_assoc()) {
                    $details[] = $detail;
                }
            }
        }

        return $details;
    }

    public function get_bids() {
        global $dbcon;
        $bids = array();
        if($query = $dbcon->prepare("SELECT bid.ID, bid.user_id AS bidder_id, (SELECT firstname FROM user WHERE ID = bid.user_id) AS bidder, sale.user_id AS seller_id, CONCAT('BID', LPAD(bid.ID, 4, 0)) AS bid_ref, (SELECT firstname FROM user WHERE ID = sale.user_id) AS seller, DATE_FORMAT(bid.created, '%d %M %Y %h:%i:%s') AS created, DATE_FORMAT(bid.created, '%d-%m-%Y') AS short_date, DATE_ADD(bid.created, INTERVAL term.duration DAY) AS maturity_date, bid.mature, CONCAT('GGD', LPAD(bid.sale_id, 4, 0)) AS sale_ref, bid.offer, bid.confirmed, bid.confirmation, (SELECT duration FROM term WHERE ID = bid.term_id) AS term, payment.bank_name, payment.account_type, payment.account_number, payment.account_holder, attachment.filename AS pop, user.phonenumber FROM bid LEFT JOIN sale ON sale.ID = bid.sale_id LEFT JOIN user ON user.ID = sale.user_id LEFT JOIN payment ON payment.ID = sale.payment_id LEFT JOIN term ON term.ID = bid.term_id LEFT JOIN attachment ON attachment.bid_id = bid.ID WHERE bid.user_id = ? ORDER BY bid.created DESC")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();
            if($result = $query->get_result()) {
                while($bid = $result->fetch_assoc()) {
                    $bids[] = $bid;
                }
            }
        } else {
            trigger_error($dbcon->error);
        }
        return $bids;
    }

    public function get_sales() {
        global $dbcon;
        $sales = array();

        if($query = $dbcon->prepare("SELECT * FROM sale WHERE user_id = ? ORDER BY created DESC")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();
            if($result = $query->get_result()) {
                while($sale = $result->fetch_assoc()) {
                    $sales[] = $sale;
                }
            }
            $query->close();
        } else {
            trigger_error($dbcon->error);
        }

        return $sales;
    }

    public function get_referred() {
        global $dbcon;
        $referred = array();

        if($query = $dbcon->prepare("SELECT referral.referral_code, user.firstname, user.surname, user.username FROM referral LEFT JOIN user ON user.ID = referral.referred_id WHERE referrer_id = ?")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();
            if($result = $query->get_result()) {
                while($user = $result->fetch_assoc()) {
                    $referred[] = $user;
                }
            }
            $query->close();
        }

        return $referred;
    }

    public function get_notifications() {
        global $dbcon;
        $notifications = array();

        if($query = $dbcon->prepare("SELECT * FROM alert WHERE user_id = ?")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();
            if($result = $query->get_result()) {
                while($notification = $result->fetch_assoc()) {
                    $notifications[] = $notification;
                }
            }
            $query->close();
        }

        return $notifications;
    }

    public function get_messages() {
        global $dbcon;
        $messages = array();

        if($query = $dbcon->prepare("SELECT * FROM message WHERE deleted = 0 AND user_id = ?")) {
            $query->bind_param("i", $this->user_id);
            $query->execute();
            if($result = $query->get_result()) {
                while($message = $result->fetch_assoc()) {
                    $messages[] = $message;
                }
            }
            $query->close();
        }

        return $messages;
    }
}