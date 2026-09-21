<?php
require_once __DIR__ . '/../../utils/IdHelper.php';

class Ride {

    // UML Class Diagram Attributes (Private)
    private $rideid;
    private $pickupLocation;
    private $destination;
    private $date;
    private $status;

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

                TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))) AS user_name,

                TRIM(CONCAT(COALESCE(drivers.first_name, ''), ' ', COALESCE(drivers.last_name, ''))) AS driver_name,

                drivers.latitude,

                drivers.longitude,

                drivers.current_location AS driver_current_location,

                vehicles.vehicle_type AS vehicle_type,

                drivers.status AS driver_status,

                payments.status AS payment_status

            FROM rides

            LEFT JOIN users
            ON rides.user_id = users.id

            LEFT JOIN drivers
            ON rides.driver_id = drivers.id

            LEFT JOIN vehicles
            ON drivers.id = vehicles.driver_id

            LEFT JOIN payments
            ON rides.id = payments.ride_id
            ORDER BY rides.id DESC
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
        $driverId = IdHelper::decodeDriver($data['driver_id'] ?? null);
        $userId = IdHelper::decodeUser($data['user_id'] ?? null);

        $stmt = $this->conn->prepare("
            INSERT INTO rides (
                driver_id,
                user_id,
                pickup_location,
                dropoff_location,
                status,
                fare,
                distance_km
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iisssdd",
            $driverId,
            $userId,
            $data['pickup_location'],
            $data['dropoff_location'],
            $data['status'],
            $data['fare'],
            $data['distance_km']
        );

        return $stmt->execute();
    }

    // UPDATE RIDE
    public function updateRide($data) {
        $driverId = IdHelper::decodeDriver($data['driver_id'] ?? null);
        $userId = IdHelper::decodeUser($data['user_id'] ?? null);
        $id = IdHelper::decodeRide($data['id'] ?? null);

        $stmt = $this->conn->prepare("
            UPDATE rides
            SET
                driver_id=?,
                user_id=?,
                pickup_location=?,
                dropoff_location=?,
                status=?,
                fare=?,
                distance_km=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "iisssddi",
            $driverId,
            $userId,
            $data['pickup_location'],
            $data['dropoff_location'],
            $data['status'],
            $data['fare'],
            $data['distance_km'],
            $id
        );

        return $stmt->execute();
    }

    // DELETE RIDE
    public function deleteRide($id) {
        $rideId = IdHelper::decodeRide($id);
        $stmt = $this->conn->prepare("DELETE FROM rides WHERE id=?");
        $stmt->bind_param("i", $rideId);

        return $stmt->execute();
    }
}
?>