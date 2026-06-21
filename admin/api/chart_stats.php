<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/Database.php';

$database = new Database();
$conn = $database->connect();

$filter = $_GET['filter'] ?? 'week';

if ($filter === 'day') {
  $sql = '
        SELECT
            HOUR(ride_date) as label,
            COUNT(*) as total_rides
        FROM rides
        WHERE DATE(ride_date)=CURDATE()
        GROUP BY HOUR(ride_date)
        ORDER BY label ASC
    ';
} elseif ($filter === 'month') {
  $sql = '
        SELECT
            DATE(ride_date) as label,
            COUNT(*) as total_rides
        FROM rides
        WHERE MONTH(ride_date)=MONTH(CURDATE())
        GROUP BY DATE(ride_date)
        ORDER BY label ASC
    ';
} else {
  $sql = '
        SELECT
            DATE(ride_date) as label,
            COUNT(*) as total_rides
        FROM rides
        WHERE YEARWEEK(ride_date, 1)=YEARWEEK(CURDATE(), 1)
        GROUP BY DATE(ride_date)
        ORDER BY label ASC
    ';
}

$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);

?>