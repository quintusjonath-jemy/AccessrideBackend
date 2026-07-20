<?php
class RideRequest
{
  // UML Class Diagram Attributes (Private)
  private $id;
  private $passenger_id;
  private $driver_id;
  private $ride_id;
  private $user_status;
  private $driver_status;
  private $request_time;

  private $conn;
  private $table = 'ride_requests';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Create a new ride request
  public function createRequest($userId, $driverId, $rideId)
  {
    $this->passenger_id = $userId;
    $this->driver_id = $driverId;
    $this->ride_id = $rideId;
    $this->user_status = 'pending';
    $this->driver_status = 'pending';

    $sql = "INSERT INTO {$this->table} (user_id, driver_id, ride_id, user_status, driver_status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("iiiss", $this->passenger_id, $this->driver_id, $this->ride_id, $this->user_status, $this->driver_status);
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
