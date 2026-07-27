<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Handle preflight OPTIONS requests
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
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/RideRequest.php';

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
  if (!$data) {
    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => 'Invalid JSON payload'
    ]);
    exit;
  }

  // Validation
  if (empty($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
  }
  if (empty($data['pickup_location'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Pickup location is required']);
    exit;
  }
  if (empty($data['dropoff_location'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dropoff location is required']);
    exit;
  }

  $db = (new Database())->connect();
  $rideModel = new Ride($db);
  $paymentModel = new Payment($db);
  $rideRequestModel = new RideRequest($db);

  $userId = (int) $data['user_id'];
  $pickup = trim($data['pickup_location']);
  $dropoff = trim($data['dropoff_location']);
  $vehicleType = isset($data['vehicle_type']) ? trim($data['vehicle_type']) : 'car';
  $distance = isset($data['distance_km']) ? (float) $data['distance_km'] : 0.0;

  // Fetch rates from settings dynamically
  $rate = 80.0;
  $settingsRes = $db->query("SELECT rate_bike, rate_three_wheeler, rate_car, rate_van FROM settings WHERE admin_id = 1 LIMIT 1");
  if ($settingsRes && $settingsRes->num_rows > 0) {
    $settingsRow = $settingsRes->fetch_assoc();
    if (strtolower($vehicleType) === 'bike') {
      $rate = (float)($settingsRow['rate_bike'] ?? 40.0);
    } else if (strtolower($vehicleType) === 'three wheeler') {
      $rate = (float)($settingsRow['rate_three_wheeler'] ?? 60.0);
    } else if (strtolower($vehicleType) === 'van') {
      $rate = (float)($settingsRow['rate_van'] ?? 100.0);
    } else {
      $rate = (float)($settingsRow['rate_car'] ?? 80.0);
    }
  } else {
    if (strtolower($vehicleType) === 'bike') {
      $rate = 40.0;
    } else if (strtolower($vehicleType) === 'three wheeler') {
      $rate = 60.0;
    } else if (strtolower($vehicleType) === 'van') {
      $rate = 100.0;
    }
  }

  $fare = $distance * $rate;
  $paymentMethod = isset($data['payment_method']) ? trim($data['payment_method']) : 'cash';
  $pickupLat = isset($data['pickup_lat']) ? (float)$data['pickup_lat'] : null;
  $pickupLng = isset($data['pickup_lng']) ? (float)$data['pickup_lng'] : null;

  // Create immediate ride
  $result = $rideModel->create($userId, $pickup, $dropoff, $fare, $vehicleType, $distance, 'pending', $pickupLat, $pickupLng);

  if ($result['success']) {
    $rideId = $result['id'];
    $driverId = isset($result['driver_id']) ? $result['driver_id'] : null;

    // Save payment details (status complete or pending depending on method)
    $paymentStatus = 'pending';
    if (strtolower($paymentMethod) === 'cash') {
      $paymentStatus = 'pending';  // Paid at completion of ride
    } else {
      $paymentStatus = 'completed';  // Simulated advance digital payment success
    }

    $paymentModel->create($rideId, $fare, $paymentMethod, $paymentStatus, $userId, $driverId);

    // Insert into ride_requests table
    $rideRequestModel->createRequest($userId, $driverId, $rideId);

    // Notify the user that their ride was booked
    insertUserNotification(
      $db,
      $userId,
      'Ride Booked',
      "Your ride from {$pickup} to {$dropoff} has been booked. Fare: LKR " . number_format($fare, 2) . ". Status: Pending.",
      'ride'
    );

    echo json_encode([
      'success' => true,
      'message' => 'Ride booked successfully',
      'ride_id' => $rideId
    ]);
  } else {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'message' => 'Failed to save booking: ' . $result['error']
    ]);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Server Error: ' . $e->getMessage()
  ]);
}
?>
