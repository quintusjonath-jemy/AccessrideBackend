<?php

class Driver {

    private $conn;
    private $table = "drivers";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL DRIVERS
    public function getDrivers() {
        $result = $this->conn->query("SELECT * FROM drivers");

        $drivers = [];

        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }

        return $drivers;
    }

    // GET ONE DRIVER
    public function getDriverById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM drivers WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // ADD DRIVER
    public function addDriver($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO drivers (name, email, status, location)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $data['name'],
            $data['email'],
            $data['status'],
            $data['location']
        );

        return $stmt->execute();
    }

    // UPDATE DRIVER
    public function updateDriver($data) {
        $stmt = $this->conn->prepare("
            UPDATE drivers
            SET name=?, email=?, status=?, location=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssssi",
            $data['name'],
            $data['email'],
            $data['status'],
            $data['location'],
            $data['id']
        );

        return $stmt->execute();
    }

    // DELETE DRIVER
    public function deleteDriver($id) {
        $stmt = $this->conn->prepare("DELETE FROM drivers WHERE id=?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // UPDATE DRIVER LOCATION
    public function updateLocation($data) {

        $sql = "
            UPDATE drivers
            SET latitude=?, longitude=?
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ddi",
            $data['latitude'],
            $data['longitude'],
            $data['id']
        );

        return $stmt->execute();
    }
}
?>