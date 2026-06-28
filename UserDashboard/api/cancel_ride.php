<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([
    'success' => false,
    'message' => 'Method Not Allowed'
  ]);
  exit;
}

require_once __DIR__ . '/../config/Database.php';

try {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!$data || empty($data['ride_id'])) {
    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => 'Ride ID is required'
    ]);
    exit;
  }

  $rideId = (int) $data['ride_id'];
  $db = (new Database())->connect();

  // Cancel the ride request if exists
  $db->query("UPDATE ride_requests SET user_status = 'cancelled' WHERE ride_id = " . $rideId . " AND user_status = 'pending'");

  $sql = 'DELETE FROM rides WHERE id = ?';
  $stmt = $db->prepare($sql);
  if (!$stmt) {
    throw new Exception('SQL prepare failed: ' . $db->error);
  }

  $stmt->bind_param('i', $rideId);
  if ($stmt->execute()) {
    echo json_encode([
      'success' => true,
      'message' => 'Ride cancelled and deleted successfully'
    ]);
  } else {
    throw new Exception('Execute failed: ' . $stmt->error);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Server Error: ' . $e->getMessage()
  ]);
}
?>
