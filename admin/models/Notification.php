<?php

class Notification {

    private $conn;
    private $table = "admin_notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL NOTIFICATIONS
    public function getNotifications() {
        $sql = "
            SELECT * FROM " . $this->table . "
            ORDER BY created_at DESC
            LIMIT 50
        ";

        $result = $this->conn->query($sql);
        $notifications = [];

        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        return $notifications;
    }

    // MARK SPECIFIC NOTIFICATION AS READ
    public function markAsRead($id) {
        $sql = "
            UPDATE " . $this->table . "
            SET is_read = 1
            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // MARK ALL NOTIFICATIONS AS READ
    public function markAllAsRead() {
        $sql = "
            UPDATE " . $this->table . "
            SET is_read = 1
            WHERE is_read = 0
        ";

        return $this->conn->query($sql);
    }

    // DELETE NOTIFICATION
    public function deleteNotification($id) {
        $sql = "
            DELETE FROM " . $this->table . "
            WHERE id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
?>
