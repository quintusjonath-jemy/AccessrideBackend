<?php

class EmergencyContact
{
    private $conn;
    private $table = "emergency_contacts";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getCount($userId)
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE user_id = ?
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }
}