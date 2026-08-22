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
  if (!$data || empty($data['ride_id']) || !isset($data['rating'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ride ID and rating are required']);
    exit;
  }

  $rideId = (int)$data['ride_id'];
  $rating  = (int)$data['rating'];
  $userId  = isset($data['user_id']) ? (int)$data['user_id'] : 0;

  if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
  }

  $db = (new Database())->connect();

  $stmt = $db->prepare("UPDATE rides SET rating = ? WHERE id = ?");
  $stmt->bind_param('ii', $rating, $rideId);
  
  if ($stmt->execute()) {
    // Get driver_id of this ride
    $stmt_driver = $db->prepare("SELECT driver_id FROM rides WHERE id = ?");
    $stmt_driver->bind_param('i', $rideId);
    $stmt_driver->execute();
    $res_driver = $stmt_driver->get_result()->fetch_assoc();
    
    if ($res_driver && !empty($res_driver['driver_id'])) {
      $driverId = (int)$res_driver['driver_id'];
      
      // Calculate new average rating for this driver
      $stmt_avg = $db->prepare("SELECT AVG(rating) AS avg_rating FROM rides WHERE driver_id = ? AND rating IS NOT NULL");
      $stmt_avg->bind_param('i', $driverId);
      $stmt_avg->execute();
      $res_avg = $stmt_avg->get_result()->fetch_assoc();
      
      if ($res_avg && $res_avg['avg_rating'] !== null) {
        $avgRating = floatval($res_avg['avg_rating']);
        
        // Update rating in drivers table
        $stmt_update_driver = $db->prepare("UPDATE drivers SET rating = ? WHERE id = ?");
        $stmt_update_driver->bind_param('di', $avgRating, $driverId);
        $stmt_update_driver->execute();
      }
    }

    // Notify the user their rating was submitted
    if ($userId > 0) {
      insertUserNotification(
        $db,
        $userId,
        'Rating Submitted',
        "You gave a {$rating}-star rating for your ride. Thank you for your feedback!",
        'success'
      );
    }

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
