<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once '../config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    $data = json_decode(file_get_contents("php://input"), true);
    $driver_id = isset($data['driver_id']) ? intval($data['driver_id']) : null;
    $location = isset($data['location']) ? trim($data['location']) : null;
    $latitude = isset($data['latitude']) ? floatval($data['latitude']) : null;
    $longitude = isset($data['longitude']) ? floatval($data['longitude']) : null;

    if (!$driver_id) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "driver_id is required"]);
        exit;
    }

    $stmt = $db->prepare("
        UPDATE drivers 
        SET current_location = ?, latitude = ?, longitude = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("sddi", $location, $latitude, $longitude, $driver_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Driver location updated"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to update location"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
