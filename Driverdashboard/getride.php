<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include "db.php";

$sql = "SELECT id, user_id, pickup_location as pickup, dropoff_location as dropoff, distance_km as distance, fare FROM rides WHERE status='pending' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    // Format distance and fare for the frontend
    $row['distance'] = $row['distance'] . " km";
    $row['fare'] = "Rs. " . number_format((float)$row['fare'], 2);
    $row['duration'] = "10 mins"; // Dummy duration since it's not in DB
    echo json_encode($row);
} else {
    echo json_encode(["message" => "No rides available"]);
}
?>