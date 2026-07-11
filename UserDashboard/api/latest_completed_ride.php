<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once __DIR__ . '/../config/Database.php';

try {
  if (empty($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
  }

  $userId = (int)$_GET['user_id'];
  $db = (new Database())->connect();

  // Make sure rating column exists in rides table
  $check_rating = $db->query("SHOW COLUMNS FROM rides LIKE 'rating'");
  if ($check_rating && $check_rating->num_rows === 0) {
    $db->query("ALTER TABLE rides ADD COLUMN rating INT DEFAULT NULL");
  }

  // Get most recent completed or accepted/active ride for this user to show completion details
  $sql = "SELECT r.*, d.name AS driver_name, v.vehicle_type, v.vehicle_number 
          FROM rides r 
          LEFT JOIN drivers d ON r.driver_id = d.id 
          LEFT JOIN vehicles v ON d.id = v.driver_id 
          WHERE r.user_id = ? 
          ORDER BY r.ride_date DESC 
          LIMIT 1";

  $stmt = $db->prepare($sql);
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
      'success' => true,
      'ride' => $row
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'message' => 'No rides found.'
    ]);
  }
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>
