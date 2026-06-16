<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);
$ride_id = isset($data['id']) ? (int)$data['id'] : null;

if ($ride_id) {
    // If we have a specific ride ID, update that one
    $sql = "UPDATE rides SET status='rejected' WHERE id = ? AND status='pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ride_id);
    $success = $stmt->execute();
} else {
    // Fallback: reject the most recent pending ride if no ID provided
    $sql = "UPDATE rides SET status='rejected' WHERE status='pending' ORDER BY id DESC LIMIT 1";
    $success = $conn->query($sql) === TRUE;
}

if ($success) {
    echo json_encode(["status" => "success", "message" => "Ride Rejected"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error updating ride"]);
}
?>