<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once "../config/Database.php";

$database = new Database();
$conn = $database->connect();

$sql = "
    SELECT
        DATE(ride_date) as ride_date,
        COUNT(*) as total_rides
    FROM rides
    GROUP BY DATE(ride_date)
    ORDER BY ride_date ASC
    LIMIT 7
";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()) {

    $data[] = $row;
}

echo json_encode($data);

?>