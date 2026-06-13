<?php

class Payment
{
    private $conn;
    private $table = "payments";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create a new payment record
    public function create($rideId, $amount, $paymentMethod, $status = "pending")
    {
        $query = "INSERT INTO {$this->table} (ride_id, amount, payment_method, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("idss", $rideId, $amount, $paymentMethod, $status);
        return $stmt->execute();
    }

    // Update payment record by ride_id
    public function updateByRideId($rideId, $amount, $paymentMethod, $status)
    {
        $query = "UPDATE {$this->table} SET amount = ?, payment_method = ?, status = ? WHERE ride_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("dssi", $amount, $paymentMethod, $status, $rideId);
        return $stmt->execute();
    }

    // Get payment details by ride_id
    public function getByRideId($rideId)
    {
        $query = "SELECT * FROM {$this->table} WHERE ride_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $rideId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
