<?php

class Ride {

    private $conn;
    private $table = "rides";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET RIDES
    public function getRides() {

        $sql = "
            SELECT

                rides.*,

                users.name AS user_name,

                drivers.name AS driver_name,

                drivers.latitude,

                drivers.longitude

            FROM rides

            LEFT JOIN users
            ON rides.user_id = users.id

            LEFT JOIN drivers
            ON rides.driver_id = drivers.id
        ";

        $result = $this->conn->query($sql);

        $rides = [];

        while ($row = $result->fetch_assoc()) {

            $rides[] = $row;
        }

        return $rides;
    }

    // ADD RIDE
    public function addRide($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO rides (driver_id, user_id, status, location)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiss",
            $data['driver_id'],
            $data['user_id'],
            $data['status'],
            $data['location']
        );

        return $stmt->execute();
    }

    // UPDATE RIDE
    public function updateRide($data) {
        $stmt = $this->conn->prepare("
            UPDATE rides
            SET driver_id=?, user_id=?, status=?, location=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "iissi",
            $data['driver_id'],
            $data['user_id'],
            $data['status'],
            $data['location'],
            $data['id']
        );

        return $stmt->execute();
    }

    // DELETE RIDE
    public function deleteRide($id) {
        $stmt = $this->conn->prepare("DELETE FROM rides WHERE id=?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
?>