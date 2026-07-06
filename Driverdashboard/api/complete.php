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
    $ride_id = isset($data['ride_id']) ? intval($data['ride_id']) : null;

    if ($ride_id) {
        // 1. Update rides status to completed
        $stmt = $db->prepare("UPDATE rides SET status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $ride_id);
        $res1 = $stmt->execute();

        // 2. Update ride_requests status to completed
        $stmt2 = $db->prepare("UPDATE ride_requests SET user_status = 'completed' WHERE ride_id = ?");
        $stmt2->bind_param("i", $ride_id);
        $res2 = $stmt2->execute();

        // 3. Update payment status to completed
        $stmt3 = $db->prepare("UPDATE payments SET status = 'completed' WHERE ride_id = ?");
        $stmt3->bind_param("i", $ride_id);
        $res3 = $stmt3->execute();

        if ($res1) {
            echo json_encode(["status" => "success", "message" => "Ride completed successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update ride status"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ride_id is required"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
