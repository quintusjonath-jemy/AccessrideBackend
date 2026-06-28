<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include "db.php";

$sql = "SELECT id, fare, status, passenger_name, passenger_initials, trip_time FROM rides WHERE status = 'completed' OR status = 'pending' ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql);

$trips = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $trips[] = [
            'initials' => $row['passenger_initials'] ? $row['passenger_initials'] : 'N/A',
            'name' => $row['passenger_name'] ? $row['passenger_name'] : 'Unknown Passenger',
            'time' => $row['trip_time'] ? $row['trip_time'] : 'Recently',
            'amount' => 'Rs. ' . number_format((float)$row['fare'], 2)
        ];
    }
}
echo json_encode($trips);
?>
