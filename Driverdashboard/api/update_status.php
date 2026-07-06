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
require_once '../models/Driver.php';

try {
    $database = new Database();
    $db = $database->connect();
    $driverModel = new Driver($db);

    $data = json_decode(file_get_contents("php://input"), true);
    $driver_id = isset($data['driver_id']) ? intval($data['driver_id']) : null;
    $status = isset($data['status']) ? trim(strtolower($data['status'])) : null;

    if (!$driver_id || !$status) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "driver_id and status are required"]);
        exit;
    }

    // Validate status values
    if (!in_array($status, ['online', 'offline', 'busy', 'blocked'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid status value"]);
        exit;
    }

    $result = $driverModel->updateStatus($driver_id, $status);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Driver status updated to " . $status]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to update driver status"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
