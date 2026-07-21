<?php

class Notification
{
  // UML Class Diagram Attributes (Private)
  private $id;
  private $passenger_id;
  private $driver_id;
  private $admin_id;
  private $title;
  private $message;
  private $is_read;
  private $created_at;

  private $conn;
  private $table = 'user_notifications';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  public function getUnreadCount($userId)
  {
    $query = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE user_id = ?
            AND is_read = 0
        ";

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param('i', $userId);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
  }
}
