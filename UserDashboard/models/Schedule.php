<?php

class Schedule
{
    private $conn;
    private $table = "rides";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Helper to check if a column exists in the rides table dynamically
    private function hasColumn($column)
    {
        $result = $this->conn->query("SHOW COLUMNS FROM {$this->table} LIKE '{$column}'");
        return $result && $result->num_rows > 0;
    }

    // Helper to get first driver in the database
    private function getDefaultDriverId()
    {
        $result = $this->conn->query("SELECT id FROM drivers LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            return (int) $row['id'];
        }
        return null; // No fallback to avoid foreign key violations
    }

    // CREATE A NEW SCHEDULED RIDE
    public function create($userId, $pickup, $dropoff, $dateTime, $fare, $vehicleType, $distance = 0.0)
    {
        $driverId = $this->getDefaultDriverId();
        if ($driverId === null) {
            return [
                "success" => false,
                "error" => "No drivers available. A driver must be assigned to schedule a ride."
            ];
        }

        // Check column availability in the database
        $hasVehicleCol = $this->hasColumn("vehicle_type");
        $hasWheelchairCol = $this->hasColumn("wheelchair_type");
        $hasDistanceCol = $this->hasColumn("distance_km");

        if ($hasVehicleCol) {
            $sql = "INSERT INTO {$this->table} (
                driver_id,
                user_id,
                pickup_location,
                dropoff_location,
                status,
                fare,
                ride_date,
                vehicle_type" . ($hasDistanceCol ? ", distance_km" : "") . "
            ) VALUES (?, ?, ?, ?, 'scheduled', ?, ?, ?" . ($hasDistanceCol ? ", ?" : "") . ")";
            
            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("iissdsds", $driverId, $userId, $pickup, $dropoff, $fare, $dateTime, $vehicleType, $distance);
            } else {
                $stmt->bind_param("iissdss", $driverId, $userId, $pickup, $dropoff, $fare, $dateTime, $vehicleType);
            }
        } else if ($hasWheelchairCol) {
            // Map vehicle type to valid wheelchair_type enum values ('manual', 'motorized', 'none')
            $wheelchairValue = "none";
            $pickupModified = $pickup;
            if (strtolower($vehicleType) === 'van') {
                $wheelchairValue = "manual";
            } else {
                $pickupModified .= " (Vehicle: " . ucfirst($vehicleType) . ")";
            }

            $sql = "INSERT INTO {$this->table} (
                driver_id,
                user_id,
                pickup_location,
                dropoff_location,
                status,
                fare,
                ride_date,
                wheelchair_type" . ($hasDistanceCol ? ", distance_km" : "") . "
            ) VALUES (?, ?, ?, ?, 'scheduled', ?, ?, ?" . ($hasDistanceCol ? ", ?" : "") . ")";

            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("iissdsds", $driverId, $userId, $pickupModified, $dropoff, $fare, $dateTime, $wheelchairValue, $distance);
            } else {
                $stmt->bind_param("iissdss", $driverId, $userId, $pickupModified, $dropoff, $fare, $dateTime, $wheelchairValue);
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
            ) VALUES (?, ?, ?, ?, 'scheduled', ?, ?" . ($hasDistanceCol ? ", ?" : "") . ")";

            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("iissdsd", $driverId, $userId, $pickupModified, $dropoff, $fare, $dateTime, $distance);
            } else {
                $stmt->bind_param("iissds", $driverId, $userId, $pickupModified, $dropoff, $fare, $dateTime);
            }
        }

        if ($stmt->execute()) {
            return [
                "success" => true,
                "id" => $stmt->insert_id
            ];
        }

        return [
            "success" => false,
            "error" => $stmt->error
        ];
    }

    // UPDATE AN EXISTING SCHEDULED RIDE
    public function update($rideId, $userId, $pickup, $dropoff, $dateTime, $fare, $vehicleType, $distance = 0.0)
    {
        $hasVehicleCol = $this->hasColumn("vehicle_type");
        $hasWheelchairCol = $this->hasColumn("wheelchair_type");
        $hasDistanceCol = $this->hasColumn("distance_km");

        if ($hasVehicleCol) {
            $sql = "UPDATE {$this->table}
                SET pickup_location = ?,
                    dropoff_location = ?,
                    ride_date = ?,
                    fare = ?,
                    vehicle_type = ?" . ($hasDistanceCol ? ", distance_km = ?" : "") . "
                WHERE id = ?
                AND user_id = ?
                AND status = 'scheduled'";
            
            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("sssdsdii", $pickup, $dropoff, $dateTime, $fare, $vehicleType, $distance, $rideId, $userId);
            } else {
                $stmt->bind_param("sssdsii", $pickup, $dropoff, $dateTime, $fare, $vehicleType, $rideId, $userId);
            }
        } else if ($hasWheelchairCol) {
            // Map vehicle type to valid wheelchair_type enum values ('manual', 'motorized', 'none')
            $wheelchairValue = "none";
            $pickupModified = $pickup;
            if (strtolower($vehicleType) === 'van') {
                $wheelchairValue = "manual";
            } else {
                $pickupModified .= " (Vehicle: " . ucfirst($vehicleType) . ")";
            }

            $sql = "UPDATE {$this->table}
                SET pickup_location = ?,
                    dropoff_location = ?,
                    ride_date = ?,
                    fare = ?,
                    wheelchair_type = ?" . ($hasDistanceCol ? ", distance_km = ?" : "") . "
                WHERE id = ?
                AND user_id = ?
                AND status = 'scheduled'";

            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("sssdsdii", $pickupModified, $dropoff, $dateTime, $fare, $wheelchairValue, $distance, $rideId, $userId);
            } else {
                $stmt->bind_param("sssdsii", $pickupModified, $dropoff, $dateTime, $fare, $wheelchairValue, $rideId, $userId);
            }
        } else {
            $pickupModified = $pickup;
            if (!empty($vehicleType) && $vehicleType !== 'none') {
                $pickupModified .= " (Vehicle: " . ucfirst($vehicleType) . ")";
            }

            $sql = "UPDATE {$this->table}
                SET pickup_location = ?,
                    dropoff_location = ?,
                    ride_date = ?,
                    fare = ?" . ($hasDistanceCol ? ", distance_km = ?" : "") . "
                WHERE id = ?
                AND user_id = ?
                AND status = 'scheduled'";

            $stmt = $this->conn->prepare($sql);
            if ($hasDistanceCol) {
                $stmt->bind_param("sssddii", $pickupModified, $dropoff, $dateTime, $fare, $distance, $rideId, $userId);
            } else {
                $stmt->bind_param("sssdii", $pickupModified, $dropoff, $dateTime, $fare, $rideId, $userId);
            }
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // GET ALL ACTIVE SCHEDULED RIDES FOR A USER
    public function getActiveSchedules($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT r.*, p.payment_method, p.status AS payment_status, p.amount AS payment_amount 
            FROM {$this->table} r
            LEFT JOIN payments p ON r.id = p.ride_id
            WHERE r.user_id = ?
            AND r.status = 'scheduled'
            ORDER BY r.ride_date ASC
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // CANCEL A SCHEDULED RIDE
    public function cancel($rideId, $userId)
    {
        // We update the status to 'cancelled' so it is kept in database history
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET status = 'cancelled'
            WHERE id = ?
            AND user_id = ?
            AND status = 'scheduled'
        ");

        $stmt->bind_param("ii", $rideId, $userId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return true;
        }

        return false;
    }
}
?>
