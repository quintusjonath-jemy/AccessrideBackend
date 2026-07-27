<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




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

// Helper: insert a row into user_notifications
function insertUserNotification($db, $userId, $title, $message, $type = 'info') {
  $stmt = $db->prepare("INSERT INTO user_notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
  if ($stmt) {
    $stmt->bind_param('isss', $userId, $title, $message, $type);
    $stmt->execute();
    $stmt->close();
  }
}

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
  $userId  = isset($data['user_id']) ? (int) $data['user_id'] : 0;
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
    // Notify the user their ride was cancelled
    if ($userId > 0) {
      insertUserNotification(
        $db,
        $userId,
        'Ride Cancelled',
        'Your ride has been cancelled successfully.',
        'warning'
      );
    }
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
