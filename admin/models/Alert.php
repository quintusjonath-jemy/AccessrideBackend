<?php

class Alert {

    private $conn;
    private $table = "alerts";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALERTS
    public function getAlerts() {

        $sql = "
            SELECT alerts.*, users.name as user_name
            FROM alerts
            LEFT JOIN users
            ON alerts.user_id = users.id

            ORDER BY alerts.created_at DESC
        ";

        $result = $this->conn->query($sql);

        $alerts = [];

        while($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }

        return $alerts;
    }

    // ADD ALERT
    public function addAlert($data) {

        $sql = "
            INSERT INTO alerts
            (user_id, driver_id, alert_type, message)

            VALUES (?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "iiss",
            $data['user_id'],
            $data['driver_id'],
            $data['alert_type'],
            $data['message']
        );

        return $stmt->execute();
    }

    // RESOLVE ALERT
    public function resolveAlert($id) {

        $sql = "
            UPDATE alerts
            SET status='resolved'
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}

?>