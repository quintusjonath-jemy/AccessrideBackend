<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

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

    $ride = $rideModel->getLatestPendingRide();

    if ($ride) {
        $ride['distance'] = $ride['distance'] . " km";
        $ride['fare'] = "Rs. " . number_format((float)$ride['fare'], 2);
        $ride['duration'] = "10 mins";
        echo json_encode($ride);
    } else {
        echo json_encode(["message" => "No rides available"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
