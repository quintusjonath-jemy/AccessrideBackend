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

        $sql = "
            INSERT INTO drivers
            (
                name,
                email,
                phone,
                vehicle_number,
                vehicle_type,
                status,
                current_location
            )

            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssss",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['vehicle_number'],
            $data['vehicle_type'],
            $data['status'],
            $data['current_location']
        );

        return $stmt->execute();
    }
    // UPDATE DRIVER
    public function updateDriver($data) {

        $sql = "
            UPDATE drivers
            SET
                name=?,
                email=?,
                phone=?,
                vehicle_number=?,
                vehicle_type=?,
                status=?,
                current_location=?
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssssi",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['vehicle_number'],
            $data['vehicle_type'],
            $data['status'],
            $data['current_location'],
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

    public function toggleDriverStatus($id) {

        $stmt = $this->conn->prepare(
            "SELECT status FROM drivers WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $driver = $stmt->get_result()->fetch_assoc();

        if (!$driver) {
            return false;
        }

        $newStatus =
            strtolower($driver['status']) === 'blocked'
            ? 'online'
            : 'blocked';

        $stmt = $this->conn->prepare(
            "UPDATE drivers SET status=? WHERE id=?"
        );

        $stmt->bind_param(
            "si",
            $newStatus,
            $id
        );

        return $stmt->execute();
    }
}
?>