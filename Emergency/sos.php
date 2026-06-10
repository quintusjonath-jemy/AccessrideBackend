<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Include Database connection
require_once("../admin/config/Database.php");

// Initialize Database
$database = new Database();
$db = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? null;
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    $driver_id = $data['driver_id'] ?? null;
    $created_at = date("Y-m-d H:i:s");

    // Validate required data
    if (empty($user_id)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "User ID is required"
        ]);
        exit;
    }

    // If no driver_id provided, try to find the driver from active ride
    if (empty($driver_id)) {
        $ride_sql = "SELECT driver_id FROM rides WHERE user_id = ? AND (status = 'accepted' OR status = 'in_progress') ORDER BY created_at DESC LIMIT 1";
        $ride_stmt = $db->prepare($ride_sql);
        $ride_stmt->bind_param("i", $user_id);
        $ride_stmt->execute();
        $ride_result = $ride_stmt->get_result();
        
        if ($ride_result->num_rows > 0) {
            $ride_row = $ride_result->fetch_assoc();
            $driver_id = $ride_row['driver_id'];
        }
        $ride_stmt->close();
    }

    // Insert SOS alert into database
    $sql = "INSERT INTO alerts 
            (user_id, driver_id, alert_type, message, latitude, longitude, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $db->error
        ]);
        exit;
    }

    $alert_type = "SOS";
    $message = "Emergency SOS Alert";

    $stmt->bind_param(
        "iissdds",
        $user_id,
        $driver_id,
        $alert_type,
        $message,
        $latitude,
        $longitude,
        $created_at
    );

    if ($stmt->execute()) {
        // Fetch driver information if driver_id exists
        $driver_info = null;
        if (!empty($driver_id)) {
            $driver_sql = "SELECT id, name, phone, latitude, longitude FROM drivers WHERE id = ?";
            $driver_stmt = $db->prepare($driver_sql);
            $driver_stmt->bind_param("i", $driver_id);
            $driver_stmt->execute();
            $driver_result = $driver_stmt->get_result();
            
            if ($driver_result->num_rows > 0) {
                $driver_info = $driver_result->fetch_assoc();
            }
            $driver_stmt->close();
        }

        echo json_encode([
            "status" => "success",
            "message" => "SOS sent successfully",
            "alert_id" => $stmt->insert_id,
            "driver_called" => !empty($driver_id),
            "driver_info" => $driver_info
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to save SOS alert: " . $stmt->error
        ]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Method not allowed"
    ]);
}

$db->close();
?>