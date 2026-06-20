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

    // GET NOTIFICATION SETTINGS
    public function getNotifications($admin_id) {
        $stmt = $this->conn->prepare("
            SELECT
                sos_alert,
                ride_alert,
                driver_alert,
                email_notifications
            FROM " . $this->table . "
            WHERE admin_id=?
        ");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $res['sos_alert'] = (int)$res['sos_alert'];
            $res['ride_alert'] = (int)$res['ride_alert'];
            $res['driver_alert'] = (int)$res['driver_alert'];
            $res['email_notifications'] = (int)$res['email_notifications'];
        }
        return $res;
    }

    // UPDATE NOTIFICATION SETTINGS
    public function updateNotifications($admin_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE " . $this->table . "
            SET
                sos_alert=?,
                ride_alert=?,
                driver_alert=?,
                email_notifications=?
            WHERE admin_id=?
        ");
        $stmt->bind_param(
            "iiiii",
            $data['sos_alert'],
            $data['ride_alert'],
            $data['driver_alert'],
            $data['email_notifications'],
            $admin_id
        );
        return $stmt->execute();
    }

    // GET SYSTEM SETTINGS
    public function getSystemSettings($admin_id) {
        $stmt = $this->conn->prepare("
            SELECT
                theme,
                refresh_rate,
                sos_enabled,
                tracking_enabled
            FROM " . $this->table . "
            WHERE admin_id=?
        ");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $res['refresh_rate'] = (int)$res['refresh_rate'];
            $res['sos_enabled'] = (int)$res['sos_enabled'];
            $res['tracking_enabled'] = (int)$res['tracking_enabled'];
        }
        return $res;
    }

    // UPDATE SYSTEM SETTINGS
    public function updateSystemSettings($admin_id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE " . $this->table . "
            SET
                theme=?,
                refresh_rate=?,
                sos_enabled=?,
                tracking_enabled=?
            WHERE admin_id=?
        ");
        $stmt->bind_param(
            "siiii",
            $data['theme'],
            $data['refresh_rate'],
            $data['sos_enabled'],
            $data['tracking_enabled'],
            $admin_id
        );
        return $stmt->execute();
    }
}
?>
