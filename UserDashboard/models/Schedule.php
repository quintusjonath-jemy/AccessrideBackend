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

    // CREATE A NEW SCHEDULED RIDE
    public function create($userId, $pickup, $dropoff, $dateTime, $fare, $vehicleType)
    {
        // Check column availability in the database
        $hasVehicleCol = $this->hasColumn("vehicle_type");
        $hasWheelchairCol = $this->hasColumn("wheelchair_type");

        if ($hasVehicleCol) {
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table} (
                    user_id,
                    pickup_location,
                    dropoff_location,
                    status,
                    fare,
                    ride_date,
                    vehicle_type
                ) VALUES (?, ?, ?, 'scheduled', ?, ?, ?)
            ");
            $stmt->bind_param("issdds", $userId, $pickup, $dropoff, $fare, $dateTime, $vehicleType);
        } else if ($hasWheelchairCol) {
            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table} (
                    user_id,
                    pickup_location,
                    dropoff_location,
                    status,
                    fare,
                    ride_date,
                    wheelchair_type
                ) VALUES (?, ?, ?, 'scheduled', ?, ?, ?)
            ");
            $stmt->bind_param("issdds", $userId, $pickup, $dropoff, $fare, $dateTime, $vehicleType);
        } else {
            // Fallback: If neither column exists, store vehicle selection in pickup_location suffix
            $pickupModified = $pickup;
            if (!empty($vehicleType) && $vehicleType !== 'none') {
                $pickupModified .= " (Vehicle: " . ucfirst($vehicleType) . ")";
            }

            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table} (
                    user_id,
                    pickup_location,
                    dropoff_location,
                    status,
                    fare,
                    ride_date
                ) VALUES (?, ?, ?, 'scheduled', ?, ?)
            ");
            $stmt->bind_param("issdd", $userId, $pickupModified, $dropoff, $fare, $dateTime);
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

    // GET ALL ACTIVE SCHEDULED RIDES FOR A USER
    public function getActiveSchedules($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ?
            AND status = 'scheduled'
            AND ride_date >= NOW()
            ORDER BY ride_date ASC
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
