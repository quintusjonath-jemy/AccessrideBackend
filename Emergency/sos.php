<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Include Database connection
require_once("../admin/config/Database.php");

// Initialize Database
$database = new Database();
$db = $database->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = $data['user_id'] ?? null;
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    $driver_id = $data['driver_id'] ?? null;

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
        $ride_sql = "SELECT driver_id FROM rides WHERE user_id = ? AND (status = 'accepted' OR status = 'active') ORDER BY id DESC LIMIT 1";
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
            (user_id, driver_id, alert_type, message, latitude, longitude, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $db->error
        ]);
        exit;
    }

    $alert_type = "sos";
    $message = "Emergency SOS Alert";

    $stmt->bind_param(
        "iissdd",
        $user_id,
        $driver_id,
        $alert_type,
        $message,
        $latitude,
        $longitude
    );

    if ($stmt->execute()) {
        // Fetch driver information if driver_id exists
        $driver_info = null;
        if (!empty($driver_id)) {
            $driver_sql = "SELECT id, first_name, last_name, phone, latitude, longitude FROM drivers WHERE id = ?";
            $driver_stmt = $db->prepare($driver_sql);
            if ($driver_stmt) {
                $driver_stmt->bind_param("i", $driver_id);
                $driver_stmt->execute();
                $driver_result = $driver_stmt->get_result();
                
                if ($driver_result->num_rows > 0) {
                    $row = $driver_result->fetch_assoc();
                    $driver_info = [
                        'id' => $row['id'],
                        'name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                        'phone' => $row['phone'] ?? '',
                        'latitude' => $row['latitude'] ?? null,
                        'longitude' => $row['longitude'] ?? null
                    ];
                }
                $driver_stmt->close();
            }
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
} elseif ($method === 'GET') {
    // Check if user_id is provided as a query string parameter
    $user_id = $_GET['user_id'] ?? null;
    if (!empty($user_id)) {
        $sql = "SELECT id, user_id, driver_id, alert_type, message, latitude, longitude, status, created_at FROM alerts WHERE user_id = ? AND alert_type = 'sos' ORDER BY created_at DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $alert = $result->fetch_assoc();
        $stmt->close();

        echo json_encode([
            "status" => "success",
            "active_sos" => $alert
        ]);
    } else {
        // Return a friendly system check message indicating connection is healthy
        echo json_encode([
            "status" => "success",
            "message" => "AccessRide SOS Service is online and connected to the database. Ready for POST requests."
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Method not allowed"
    ]);
}

$db->close();
?>