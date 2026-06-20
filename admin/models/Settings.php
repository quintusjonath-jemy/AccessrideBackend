<?php

class Settings {
    private $conn;
    private $table = "settings";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET SETTINGS FOR AN ADMIN
    public function getSettingsByAdminId($admin_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE admin_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Format properties
            $row['id'] = (int)$row['id'];
            $row['admin_id'] = (int)$row['admin_id'];
            $row['sos_alert'] = (int)$row['sos_alert'];
            $row['ride_alert'] = (int)$row['ride_alert'];
            $row['driver_alert'] = (int)$row['driver_alert'];
            $row['email_notifications'] = (int)$row['email_notifications'];
            $row['refresh_rate'] = (int)$row['refresh_rate'];
            $row['sos_enabled'] = (int)$row['sos_enabled'];
            $row['tracking_enabled'] = (int)$row['tracking_enabled'];
            
            return $row;
        }

        return null;
    }
}
?>
