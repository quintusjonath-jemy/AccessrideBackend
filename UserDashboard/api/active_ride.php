<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

require_once __DIR__ . "/../config/Database.php";

try {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "User ID is required"
        ]);
        exit;
    }

    $userId = (int) $_GET['user_id'];
    $db = (new Database())->connect();

    // Query to find the latest active, accepted or pending ride
    $sql = "
        SELECT 
            r.*, 
            TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS driver_name, 
            d.phone AS driver_phone, 
            d.vehicle_number AS driver_vehicle_number, 
            d.vehicle_type AS driver_vehicle_type, 
            d.latitude AS driver_lat, 
            d.longitude AS driver_lng
        FROM rides r
        LEFT JOIN drivers d ON r.driver_id = d.id
        WHERE r.user_id = ?
        AND r.status IN ('pending', 'accepted', 'active')
        ORDER BY r.id DESC
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $db->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();

    if ($ride) {
        // Ensure numeric variables are correctly typed
        $ride['id'] = (int) $ride['id'];
        $ride['driver_id'] = (int) $ride['driver_id'];
        $ride['user_id'] = (int) $ride['user_id'];
        $ride['fare'] = (float) $ride['fare'];
        $ride['distance_km'] = (float) $ride['distance_km'];
        if ($ride['driver_lat'] !== null) $ride['driver_lat'] = (float) $ride['driver_lat'];
        if ($ride['driver_lng'] !== null) $ride['driver_lng'] = (float) $ride['driver_lng'];
        
        echo json_encode([
            "success" => true,
            "data" => $ride
        ]);
    } else {
        // Fallback or empty state
        echo json_encode([
            "success" => true,
            "data" => null,
            "message" => "No active rides found."
        ]);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server Error: " . $e->getMessage()
    ]);
}
?>
