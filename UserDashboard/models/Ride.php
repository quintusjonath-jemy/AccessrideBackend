<?php

class Ride
{
    private $conn;
    private $table = "rides";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getTotalRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM rides
             WHERE user_id = ?"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getCompletedRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM rides
             WHERE user_id = ?
             AND status = 'completed'"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getPendingRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM rides
             WHERE user_id = ?
             AND status = 'pending'"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getUpcomingRide($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
            FROM rides
            WHERE user_id = ?
            AND ride_date >= NOW()
            AND status IN ('pending','accepted')
            ORDER BY ride_date ASC
            LIMIT 1"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getRecentRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM rides
             WHERE user_id = ?
             ORDER BY ride_date DESC
             LIMIT 5"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Helper to check if a column exists in the rides table dynamically
    private function hasColumn($column)
    {
        $result = $this->conn->query("SHOW COLUMNS FROM {$this->table} LIKE '{$column}'");
        return $result && $result->num_rows > 0;
    }

    // Helper to get first driver in the database matching vehicle type
    private function getDefaultDriverId($vehicleType = null)
    {
        if ($vehicleType) {
            $stmt = $this->conn->prepare("
                SELECT d.id 
                FROM drivers d 
                JOIN vehicles v ON d.id = v.driver_id 
                WHERE LOWER(v.vehicle_type) = LOWER(?) 
                LIMIT 1
            ");
            if ($stmt) {
                $stmt->bind_param("s", $vehicleType);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    return (int) $row['id'];
                }
            }
        }

        $result = $this->conn->query("SELECT id FROM drivers LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            return (int) $row['id'];
        }
        return null;
    }

    // CREATE A NEW IMMEDIATE RIDE
    public function create($userId, $pickup, $dropoff, $fare, $vehicleType, $distance = 0.0, $status = 'pending')
    {
        $driverId = $this->getDefaultDriverId($vehicleType);
        if ($driverId === null) {
            return [
                "success" => false,
                "error" => "No drivers available. A driver must be assigned to book a ride."
            ];
        }

        $hasVehicleCol = $this->hasColumn("vehicle_type");
        $hasDistanceCol = $this->hasColumn("distance_km");
        $dateTime = date("Y-m-d H:i:s");

        if ($hasVehicleCol) {
            $sql = "INSERT INTO {$this->table} (
                driver_id,
                user_id,
                pickup_location,
                dropoff_location,
                status,
                fare,
                ride_date" . ($hasDistanceCol ? ", distance_km" : "") . ",
                vehicle_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?" . ($hasDistanceCol ? ", ?" : "") . ", ?)";
            
            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("iisssdsds", $driverId, $userId, $pickup, $dropoff, $status, $fare, $dateTime, $distance, $vehicleType);
            } else {
                $stmt->bind_param("iisssdss", $driverId, $userId, $pickup, $dropoff, $status, $fare, $dateTime, $vehicleType);
            }
        } else {
            // Fallback: If neither column exists, store vehicle selection in pickup_location suffix
            $pickupModified = $pickup;
            if (!empty($vehicleType) && $vehicleType !== 'none') {
                $pickupModified .= " (Vehicle: " . ucfirst($vehicleType) . ")";
            }

            $sql = "INSERT INTO {$this->table} (
                driver_id,
                user_id,
                pickup_location,
                dropoff_location,
                status,
                fare,
                ride_date" . ($hasDistanceCol ? ", distance_km" : "") . "
            ) VALUES (?, ?, ?, ?, ?, ?, ?" . ($hasDistanceCol ? ", ?" : "") . ")";

            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("iisssdsd", $driverId, $userId, $pickupModified, $dropoff, $status, $fare, $dateTime, $distance);
            } else {
                $stmt->bind_param("iisssds", $driverId, $userId, $pickupModified, $dropoff, $status, $fare, $dateTime);
            }
        }

        if ($stmt->execute()) {
            return [
                "success" => true,
                "id" => $stmt->insert_id,
                "driver_id" => $driverId
            ];
        }

        return [
            "success" => false,
            "error" => $stmt->error
        ];
    }
}