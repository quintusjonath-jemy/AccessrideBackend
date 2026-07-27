<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/Database.php';

// DATABASE CONNECTION
$database = new Database();

$conn = $database->connect();

// TOTAL DRIVERS
$driversQuery = '
    SELECT COUNT(*) as totalDrivers
    FROM drivers
';

$driversResult = $conn->query($driversQuery);

$totalDrivers = $driversResult->fetch_assoc()['totalDrivers'];

// TOTAL RIDES
$ridesQuery = '
    SELECT COUNT(*) as totalRides
    FROM rides
';

$ridesResult = $conn->query($ridesQuery);

$totalRides = $ridesResult->fetch_assoc()['totalRides'];

// ACTIVE RIDES
$activeQuery = "
    SELECT COUNT(*) as activeRides
    FROM rides
    WHERE status = 'active'
";

$activeResult = $conn->query($activeQuery);

$activeRides = $activeResult->fetch_assoc()['activeRides'];

// RETURN JSON
echo json_encode([
  'totalDrivers' => $totalDrivers,
  'totalRides' => $totalRides,
  'activeRides' => $activeRides
]);

?>