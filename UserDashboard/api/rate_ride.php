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

require_once __DIR__ . '/../config/Database.php';

try {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!$data || empty($data['ride_id']) || !isset($data['rating'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ride ID and rating are required']);
    exit;
  }

  $rideId = (int)$data['ride_id'];
  $rating = (int)$data['rating'];

  if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
  }

  $db = (new Database())->connect();

  // Make sure rating column exists in rides table
  $check_rating = $db->query("SHOW COLUMNS FROM rides LIKE 'rating'");
  if ($check_rating && $check_rating->num_rows === 0) {
    $db->query("ALTER TABLE rides ADD COLUMN rating INT DEFAULT NULL");
  }

  $stmt = $db->prepare("UPDATE rides SET rating = ? WHERE id = ?");
  $stmt->bind_param('ii', $rating, $rideId);
  
  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to update rating']);
  }
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>
