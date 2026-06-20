<?php

class Subscription {
    private $conn;
    private $table = "subscriptions";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL SUBSCRIPTIONS
    public function getSubscriptions() {
        $sql = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($sql);
        $subscriptions = [];

        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int)$row['id'];
            $row['driver_id'] = (int)$row['driver_id'];
            $row['amount'] = (float)$row['amount'];
            $subscriptions[] = $row;
        }

        return $subscriptions;
    }

    // GET SUBSCRIPTION BY DRIVER ID
    public function getSubscriptionByDriverId($driver_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE driver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Format properties
            $row['id'] = (int)$row['id'];
            $row['driver_id'] = (int)$row['driver_id'];
            $row['amount'] = (float)$row['amount'];
            
            return $row;
        }

        return null;
    }

    // ADD SUBSCRIPTION
    public function addSubscription($data) {
        $sql = "INSERT INTO " . $this->table . " (driver_id, status, expires_at, last_payment_date, amount) VALUES (?, ?, ?, ?, ?)";
        
        $status = isset($data['status']) ? $data['status'] : 'none';
        $expires = !empty($data['expires_at']) ? $data['expires_at'] : null;
        $last_pay = !empty($data['last_payment_date']) ? $data['last_payment_date'] : null;
        $amount = isset($data['amount']) ? (float)$data['amount'] : 29.99;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "isssd",
            $data['driver_id'],
            $status,
            $expires,
            $last_pay,
            $amount
        );
        return $stmt->execute();
    }

    // UPDATE SUBSCRIPTION
    public function updateSubscription($data) {
        // Robust check: if record exists, update. Otherwise, insert.
        $driver_id = (int)$data['driver_id'];
        $check_sql = "SELECT id FROM " . $this->table . " WHERE driver_id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $driver_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();

        $status = isset($data['status']) ? $data['status'] : 'none';
        $expires = !empty($data['expires_at']) ? $data['expires_at'] : null;
        $last_pay = !empty($data['last_payment_date']) ? $data['last_payment_date'] : null;
        $amount = isset($data['amount']) ? (float)$data['amount'] : 29.99;

        if ($check_res->num_rows > 0) {
            // Update
            $sql = "UPDATE " . $this->table . " SET status = ?, expires_at = ?, last_payment_date = ?, amount = ? WHERE driver_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssdi",
                $status,
                $expires,
                $last_pay,
                $amount,
                $driver_id
            );
        } else {
            // Insert
            $sql = "INSERT INTO " . $this->table . " (driver_id, status, expires_at, last_payment_date, amount) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "isssd",
                $driver_id,
                $status,
                $expires,
                $last_pay,
                $amount
            );
        }

        return $stmt->execute();
    }

    // DELETE SUBSCRIPTION
    public function deleteSubscription($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
