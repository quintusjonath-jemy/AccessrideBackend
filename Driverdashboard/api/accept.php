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
require_once '../models/Ride.php';

try {
    $database = new Database();
    $db = $database->connect();
    $rideModel = new Ride($db);

    $data = json_decode(file_get_contents("php://input"), true);
    $driver_id = isset($data['driver_id']) ? intval($data['driver_id']) : null;
    $ride_id = isset($data['ride_id']) ? intval($data['ride_id']) : null;

    if ($driver_id && $ride_id) {
        $result = $rideModel->acceptRide($driver_id, $ride_id);
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Ride Accepted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error accepting ride"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "driver_id and ride_id are required"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
