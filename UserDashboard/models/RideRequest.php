<?php
class RideRequest
{
  private $conn;
  private $table = 'ride_requests';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Create a new ride request
  public function createRequest($userId, $driverId, $rideId)
  {
    $sql = "INSERT INTO {$this->table} (user_id, driver_id, ride_id, user_status, driver_status) VALUES (?, ?, ?, 'pending', 'pending')";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("iii", $userId, $driverId, $rideId);
    return $stmt->execute();
  }

  // Get active request for a user
  public function getActiveRequest($userId)
  {
    $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND user_status = 'pending' ORDER BY id DESC LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return null;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  // Accept a ride request
  public function acceptRequest($requestId, $driverId)
  {
    $sql = "UPDATE {$this->table} SET driver_status = 'accepted', driver_id = ?, accepted_at = NOW() WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("ii", $driverId, $requestId);
    return $stmt->execute();
  }

  // Cancel a ride request
  public function cancelRequest($requestId)
  {
    $sql = "UPDATE {$this->table} SET user_status = 'cancelled' WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("i", $requestId);
    return $stmt->execute();
  }
}
?>
