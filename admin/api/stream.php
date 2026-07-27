<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Prevent caching and compression, keeping the event stream active
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Disable output buffering
if (function_exists('apache_setenv')) {
  apache_setenv('no-gzip', '1');
}
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while (ob_get_level()) {
  ob_end_flush();
}
ob_implicit_flush(true);

include_once '../config/Database.php';
include_once '../models/Alert.php';
include_once '../models/Ride.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
  echo 'data: ' . json_encode(['error' => 'Database connection failed']) . "\n\n";
  exit;
}

$alertModel = new Alert($db);
$rideModel = new Ride($db);
$userModel = new User($db);

$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$lastAlertHash = '';
$lastRideHash = '';
$lastUserHash = '';
$lastStatsHash = '';

// Run infinite loop for SSE stream
while (true) {
  // If the browser/client disconnects, stop execution to prevent CPU spin
  if (connection_aborted()) {
    break;
  }

  // Stream Alerts
  if ($type === 'alerts' || $type === 'all') {
    $alerts = $alertModel->getAlerts();
    $alertsJson = json_encode($alerts);
    $alertsHash = md5($alertsJson);

    if ($alertsHash !== $lastAlertHash) {
      if ($type === 'all') {
        echo "event: alerts\n";
      }
      echo 'data: ' . $alertsJson . "\n\n";
      $lastAlertHash = $alertsHash;
    }
  }

  // Stream Rides
  if ($type === 'rides' || $type === 'all') {
    $rides = $rideModel->getRides();
    $ridesJson = json_encode($rides);
    $ridesHash = md5($ridesJson);

    if ($ridesHash !== $lastRideHash) {
      if ($type === 'all') {
        echo "event: rides\n";
      }
      echo 'data: ' . $ridesJson . "\n\n";
      $lastRideHash = $ridesHash;
    }
  }

  // Stream Users
  if ($type === 'all') {
    $users = $userModel->getUsers();
    $usersJson = json_encode($users);
    $usersHash = md5($usersJson);

    if ($usersHash !== $lastUserHash) {
      echo "event: users\n";
      echo 'data: ' . $usersJson . "\n\n";
      $lastUserHash = $usersHash;
    }
  }

  // Stream Stats
  if ($type === 'all') {
    $driversQuery = $db->query("SELECT COUNT(*) as total FROM drivers");
    $totalDrivers = $driversQuery ? intval($driversQuery->fetch_assoc()['total']) : 0;

    $ridesQuery = $db->query("SELECT COUNT(*) as total FROM rides");
    $totalRides = $ridesQuery ? intval($ridesQuery->fetch_assoc()['total']) : 0;

    $activeQuery = $db->query("SELECT COUNT(*) as total FROM rides WHERE status = 'active'");
    $activeRides = $activeQuery ? intval($activeQuery->fetch_assoc()['total']) : 0;

    $stats = [
      'totalDrivers' => $totalDrivers,
      'totalRides' => $totalRides,
      'activeRides' => $activeRides
    ];

    $statsJson = json_encode($stats);
    $statsHash = md5($statsJson);

    if ($statsHash !== $lastStatsHash) {
      echo "event: stats\n";
      echo 'data: ' . $statsJson . "\n\n";
      $lastStatsHash = $statsHash;
    }
  }

  // Heartbeat comment to keep the connection alive
  echo ": ping\n\n";

  if (ob_get_length() > 0) {
    ob_flush();
  }
  flush();

  // Sleep for 2 seconds to reduce database and CPU overhead
  sleep(2);
}
?>
