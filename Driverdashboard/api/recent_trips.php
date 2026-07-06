<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once '../config/Database.php';
require_once '../models/Ride.php';

try {
    $database = new Database();
    $db = $database->connect();
    $rideModel = new Ride($db);

    $driver_id = isset($_GET['driver_id']) ? intval($_GET['driver_id']) : null;

    $tripsData = $rideModel->getRecentTrips($driver_id);
    $trips = [];

    foreach ($tripsData as $row) {
        $trips[] = [
            'initials' => $row['passenger_initials'] ? $row['passenger_initials'] : 'N/A',
            'name' => $row['passenger_name'] ? $row['passenger_name'] : 'Unknown Passenger',
            'time' => $row['ride_date'] ? date('h:i A', strtotime($row['ride_date'])) : 'Recently',
            'amount' => 'Rs. ' . number_format((float)$row['fare'], 2)
        ];
    }

    echo json_encode($trips);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
