<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once "../config/Database.php";

$database = new Database();

$conn = $database->connect();

$data = [];


/* USERS */

$userSql = "
    SELECT
        DATE(created_at) as created_date,
        COUNT(*) as total_users
    FROM users
    GROUP BY DATE(created_at)
    ORDER BY created_date ASC
    LIMIT 7
";

$userResult = $conn->query($userSql);

$users = [];

while($row = $userResult->fetch_assoc()) {

    $users[] = $row;
}


/* DRIVERS */

$driverSql = "
    SELECT
        DATE(created_at) as created_date,
        COUNT(*) as total_drivers
    FROM drivers
    GROUP BY DATE(created_at)
    ORDER BY created_date ASC
    LIMIT 7
";

$driverResult = $conn->query($driverSql);

$drivers = [];

while($row = $driverResult->fetch_assoc()) {

    $drivers[] = $row;
}


$data['users'] = $users;

$data['drivers'] = $drivers;

echo json_encode($data);

?>