<?php
class auction {
    public function open_sales($user_id) {
        global $dbcon;
        $sales = array();

        if($query = $dbcon->prepare("SELECT sale.ID, sale.allocated, sale.available, CONCAT(user.firstname, ' ', user.surname) AS seller, sale.user_id AS seller_id, (SELECT COUNT(*) FROM relationship WHERE (friend_a = ? AND friend_b = sale.user_id) OR (friend_a = sale.user_id AND friend_b = ?)) AS relationship, CONCAT('GGD', LPAD(sale.ID, 4, 0)) AS reference, payment.bank_name FROM sale LEFT JOIN user ON user.ID = sale.user_id LEFT JOIN payment ON payment.ID = sale.payment_id WHERE sale.deleted = 0 AND sale.suspended = 0 AND sale.expired = 0 AND sale.available >= 200 AND TIMESTAMPDIFF(HOUR, sale.created, CURRENT_TIMESTAMP) > 12 OR override = 1 ORDER BY ID DESC")) {
            $query->bind_param("ii", $user_id, $user_id);
            $query->execute();
            $result = $query->get_result();
            while($sale = $result->fetch_assoc()) {
                $sales[] = $sale;
            }
            $query->close();
        } else {
            trigger_error($dbcon->error);
        }

        return $sales;
    }

    public function open_bids($user_id) {
        global $dbcon;
        $deposits = array();

        if($query = $dbcon->prepare("SELECT bid.ID, user.firstname AS bidder, user.phonenumber, bid.user_id AS bidder_id, bid.offer, CONCAT('BID', LPAD(bid.ID, 4, 0)) AS deposit_ref, bid.sale_id, attachment.filename AS pop FROM bid LEFT JOIN sale ON bid.sale_id = sale.ID LEFT JOIN user ON user.ID = bid.user_id LEFT JOIN attachment ON attachment.bid_id = bid.ID WHERE bid.deleted = 0 AND bid.confirmed = 0 AND sale.user_id = ?")) {
            $query->bind_param("i", $user_id);
            $query->execute();
            if($result = $query->get_result()) {
                while($deposit = $result->fetch_assoc()) {
                    $deposits[] = $deposit;
                }
            }
            $query->close();
        } else {
            trigger_error($dbcon->error);
        }

        return $deposits;
    }

    public function get_terms() {
        global $dbcon;
        $terms = array();

        if($query = $dbcon->prepare("SELECT * FROM term WHERE activated = 1")) {
            $query->execute();
            $result = $query->get_result();
            
            while($term = $result->fetch_assoc()) {
                $terms[] = $term;
            }

            $query->close();
        }

        return $terms;
    }
}