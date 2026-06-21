<?php

class Payment
{
  private $conn;
  private $table = 'payments';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Create a new payment record
  public function create($rideId, $amount, $paymentMethod, $status = 'pending', $userId = null, $driverId = null)
  {
    if ($userId === null || $driverId === null) {
      $q = 'SELECT user_id, driver_id FROM rides WHERE id = ?';
      $s = $this->conn->prepare($q);
      if ($s) {
        $s->bind_param('i', $rideId);
        $s->execute();
        $res = $s->get_result()->fetch_assoc();
        if ($res) {
          if ($userId === null) {
            $userId = $res['user_id'];
          }
          if ($driverId === null) {
            $driverId = $res['driver_id'];
          }
        }
      }
    }

    $query = "INSERT INTO {$this->table} (ride_id, user_id, driver_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($query);
    if (!$stmt) {
      return false;
    }

    $stmt->bind_param('iiidss', $rideId, $userId, $driverId, $amount, $paymentMethod, $status);
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

    $stmt->bind_param('dssi', $amount, $paymentMethod, $status, $rideId);
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

    $stmt->bind_param('i', $rideId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }
}
?>
