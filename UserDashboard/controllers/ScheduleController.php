<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../models/Schedule.php";

class ScheduleController
{
    private $scheduleModel;

    public function __construct()
    {
        $db = (new Database())->connect();
        $this->scheduleModel = new Schedule($db);
    }

    // GET ALL ACTIVE SCHEDULES
    public function getSchedules($userId)
    {
        if (empty($userId)) {
            return [
                "success" => false,
                "message" => "User ID is required"
            ];
        }

        $schedules = $this->scheduleModel->getActiveSchedules($userId);

        return [
            "success" => true,
            "data" => $schedules
        ];
    }

    // ADD A NEW RIDE TO THE SCHEDULE
    public function addSchedule($data)
    {
        // 1. Validation
        if (empty($data['user_id'])) {
            return ["success" => false, "message" => "User ID is required"];
        }
        if (empty($data['pickup_location'])) {
            return ["success" => false, "message" => "Pickup location is required"];
        }
        if (empty($data['dropoff_location'])) {
            return ["success" => false, "message" => "Dropoff location is required"];
        }
        if (empty($data['ride_date'])) {
            return ["success" => false, "message" => "Ride date and time is required"];
        }

        // Validate that the scheduled date is in the future
        $scheduledTime = strtotime($data['ride_date']);
        if ($scheduledTime === false || $scheduledTime <= time()) {
            return ["success" => false, "message" => "Scheduled date must be in the future"];
        }

        $userId = (int) $data['user_id'];
        $pickup = trim($data['pickup_location']);
        $dropoff = trim($data['dropoff_location']);
        $dateTime = date("Y-m-d H:i:s", $scheduledTime);
        $fare = isset($data['fare']) ? (float) $data['fare'] : 250.0;
        
        // Read vehicle_type (with fallback to wheelchair_type if sent by legacy code)
        $vehicleType = "car";
        if (isset($data['vehicle_type'])) {
            $vehicleType = trim($data['vehicle_type']);
        } else if (isset($data['wheelchair_type'])) {
            $wt = trim($data['wheelchair_type']);
            if ($wt === "manual" || $wt === "motorized") {
                $vehicleType = "van";
            }
        }

        // Validate vehicle type
        $allowedVehicles = ["car", "van", "three wheeler", "bike"];
        if (!in_array(strtolower($vehicleType), $allowedVehicles)) {
            return ["success" => false, "message" => "Invalid vehicle type chosen"];
        }

        // 2. Call Model
        $result = $this->scheduleModel->create($userId, $pickup, $dropoff, $dateTime, $fare, $vehicleType);

        if ($result['success']) {
            return [
                "success" => true,
                "message" => "Ride scheduled successfully",
                "ride_id" => $result['id']
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to schedule ride: " . $result['error']
        ];
    }

    // UPDATE AN EXISTING SCHEDULED RIDE
    public function updateSchedule($data)
    {
        // 1. Validation
        if (empty($data['ride_id'])) {
            return ["success" => false, "message" => "Ride ID is required"];
        }
        if (empty($data['user_id'])) {
            return ["success" => false, "message" => "User ID is required"];
        }
        if (empty($data['pickup_location'])) {
            return ["success" => false, "message" => "Pickup location is required"];
        }
        if (empty($data['dropoff_location'])) {
            return ["success" => false, "message" => "Dropoff location is required"];
        }
        if (empty($data['ride_date'])) {
            return ["success" => false, "message" => "Ride date and time is required"];
        }

        $scheduledTime = strtotime($data['ride_date']);
        if ($scheduledTime === false || $scheduledTime <= time()) {
            return ["success" => false, "message" => "Scheduled date must be in the future"];
        }

        $rideId = (int) $data['ride_id'];
        $userId = (int) $data['user_id'];
        $pickup = trim($data['pickup_location']);
        $dropoff = trim($data['dropoff_location']);
        $dateTime = date("Y-m-d H:i:s", $scheduledTime);
        $fare = isset($data['fare']) ? (float) $data['fare'] : 250.0;
        
        $vehicleType = "car";
        if (isset($data['vehicle_type'])) {
            $vehicleType = trim($data['vehicle_type']);
        }

        $allowedVehicles = ["car", "van", "three wheeler", "bike"];
        if (!in_array(strtolower($vehicleType), $allowedVehicles)) {
            return ["success" => false, "message" => "Invalid vehicle type chosen"];
        }

        // 2. Call Model
        $success = $this->scheduleModel->update($rideId, $userId, $pickup, $dropoff, $dateTime, $fare, $vehicleType);

        if ($success) {
            return [
                "success" => true,
                "message" => "Scheduled ride updated successfully"
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to update scheduled ride or ride not found"
        ];
    }

    // CANCEL A SCHEDULED RIDE
    public function cancelSchedule($rideId, $userId)
    {
        if (empty($rideId) || empty($userId)) {
            return [
                "success" => false,
                "message" => "Ride ID and User ID are required"
            ];
        }

        $success = $this->scheduleModel->cancel((int) $rideId, (int) $userId);

        if ($success) {
            return [
                "success" => true,
                "message" => "Scheduled ride cancelled successfully"
            ];
        }

        return [
            "success" => false,
            "message" => "Failed to cancel ride or ride not found"
        ];
    }
}
?>
