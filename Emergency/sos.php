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
        echo json_encode([
            "status" => "success",
            "message" => "SOS sent successfully",
            "alert_id" => $stmt->insert_id
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