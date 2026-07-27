<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once __DIR__ . '/../controllers/ScheduleController.php';

try {
  $controller = new ScheduleController();
  $method = $_SERVER['REQUEST_METHOD'];

  switch ($method) {
    case 'GET':
      // Fetch scheduled rides: GET ?user_id=X
      if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode([
          'success' => false,
          'message' => 'User ID is required'
        ]);
        exit;
      }
      $userId = (int) $_GET['user_id'];
      $response = $controller->getSchedules($userId);
      echo json_encode($response);
      break;

    case 'POST':
      // Schedule a new ride
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data) {
        http_response_code(400);
        echo json_encode([
          'success' => false,
          'message' => 'Invalid JSON payload'
        ]);
        exit;
      }

      $response = $controller->addSchedule($data);
      echo json_encode($response);
      break;

    case 'PUT':
      // Update an existing scheduled ride
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data) {
        http_response_code(400);
        echo json_encode([
          'success' => false,
          'message' => 'Invalid JSON payload'
        ]);
        exit;
      }

      $response = $controller->updateSchedule($data);
      echo json_encode($response);
      break;

    case 'DELETE':
      // Cancel scheduled ride via DELETE
      // Reading query parameters e.g. DELETE ?ride_id=X&user_id=Y
      $rideId = isset($_GET['ride_id']) ? (int) $_GET['ride_id'] : 0;
      $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

      // Or fallback to JSON body if parameters aren't in the URL
      if ($rideId === 0 || $userId === 0) {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
          $rideId = isset($data['ride_id']) ? (int) $data['ride_id'] : $rideId;
          $userId = isset($data['user_id']) ? (int) $data['user_id'] : $userId;
        }
      }

      if ($rideId === 0 || $userId === 0) {
        http_response_code(400);
        echo json_encode([
          'success' => false,
          'message' => 'Ride ID and User ID are required'
        ]);
        exit;
      }

      $response = $controller->cancelSchedule($rideId, $userId);
      echo json_encode($response);
      break;

    default:
      http_response_code(405);
      echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
      ]);
      break;
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Server Error: ' . $e->getMessage()
  ]);
}
?>
