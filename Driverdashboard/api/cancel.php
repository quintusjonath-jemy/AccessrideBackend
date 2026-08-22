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

    $data = json_decode(file_get_contents("php://input"), true) ?: [];
    $ride_id = isset($data['ride_id']) ? intval($data['ride_id']) : null;
    $driver_id = isset($data['driver_id']) ? intval($data['driver_id']) : null;

    if ($ride_id) {
        $result = $rideModel->cancelRide($ride_id);
    } elseif ($driver_id) {
        $stmt = $db->prepare("SELECT id FROM rides WHERE driver_id = ? AND (status='accepted' OR status='active') ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res && !empty($res['id'])) {
            $result = $rideModel->cancelRide((int)$res['id']);
        } else {
            $result = false;
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ride_id or driver_id is required to cancel a ride"]);
        exit;
    }

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Ride cancelled"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error cancelling ride"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
