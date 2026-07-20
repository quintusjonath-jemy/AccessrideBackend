<?php

class Alert
{
  // UML Class Diagram Attributes (Protected)
  protected $alertid;
  protected $alertType;
  protected $message;
  protected $created_at;

  private $conn;
  private $table = 'alerts';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // GET ALERTS
  public function getAlerts()
  {
    $sql = "
            SELECT 
                alerts.*, 
                TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) as user_name,
                users.phone as user_phone,
                users.location as user_location,
                drivers.phone as driver_phone,
                TRIM(CONCAT(COALESCE(drivers.first_name, ''), ' ', COALESCE(drivers.last_name, ''))) as driver_name,
                (SELECT contact_name FROM emergency_contacts WHERE user_id = alerts.user_id LIMIT 1) AS emergency_contact_name,
                (SELECT phone_number FROM emergency_contacts WHERE user_id = alerts.user_id LIMIT 1) AS emergency_contact_phone
            FROM alerts
            LEFT JOIN users ON alerts.user_id = users.id
            LEFT JOIN drivers ON alerts.driver_id = drivers.id
            ORDER BY alerts.created_at DESC
        ";

    $result = $this->conn->query($sql);

    $alerts = [];

    while ($row = $result->fetch_assoc()) {
      $alerts[] = $row;
    }

    return $alerts;
  }

  // ADD ALERT
  public function addAlert($data)
  {
    $this->alertType = $data['alert_type'] ?? '';
    $this->message = $data['message'] ?? '';

    $sql = '
            INSERT INTO alerts
            (user_id, driver_id, alert_type, message)

            VALUES (?, ?, ?, ?)
        ';

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
      'iiss',
      $data['user_id'],
      $data['driver_id'],
      $this->alertType,
      $this->message
    );

    return $stmt->execute();
  }

  // RESOLVE ALERT
  public function resolveAlert($id)
  {
    $this->alertid = (int)$id;

    $sql = "
            UPDATE alerts
            SET status='resolved'
            WHERE id=?
        ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param('i', $this->alertid);

    return $stmt->execute();
  }
}

?>