<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

require_once '../config/Database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $rideId  = isset($data['ride_id'])  ? (int)$data['ride_id']  : 0;
    $otp     = isset($data['otp'])      ? (int)$data['otp']      : 0;
    $driverId = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;

    if (!$rideId || !$otp || !$driverId) {
        echo json_encode(['status' => 'error', 'message' => 'ride_id, otp, and driver_id are required']);
        exit;
    }

    // Validate OTP using the same deterministic formula used in the frontend
    $expectedOtp = (($rideId * 127 + 3571) % 9000) + 1000;
    if ($otp !== $expectedOtp) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please ask the passenger to share their code.']);
        exit;
    }

    $database = new Database();
    $db = $database->connect();

    // Set ride status to 'active'
    $stmt = $db->prepare("UPDATE rides SET status = 'active' WHERE id = ? AND driver_id = ?");
    $stmt->bind_param("ii", $rideId, $driverId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Ride not found or not assigned to this driver']);
        exit;
    }

    // Mark driver_status as 'arrived' in ride_requests
    $stmt2 = $db->prepare("UPDATE ride_requests SET driver_status = 'arrived' WHERE ride_id = ? AND driver_id = ?");
    $stmt2->bind_param("ii", $rideId, $driverId);
    $stmt2->execute();

    echo json_encode(['status' => 'success', 'message' => 'OTP verified. Ride is now active!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
