<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include "db.php"; // your database connection

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($q === "") {
    echo json_encode([
        "users" => [],
        "drivers" => [],
        "rides" => []
    ]);
    exit;
}

// escape input
$search = "%$q%";

/* ---------------- USERS ---------------- */
$userStmt = $conn->prepare("
    SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, location
    FROM users
    WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR location LIKE ?
    LIMIT 5
");
$userStmt->bind_param("ssss", $search, $search, $search, $search);
$userStmt->execute();
$users = $userStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------------- DRIVERS ---------------- */
$driverStmt = $conn->prepare("
    SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, phone, vehicle_number
    FROM drivers
    WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR vehicle_number LIKE ?
    LIMIT 5
");
$driverStmt->bind_param("sssss", $search, $search, $search, $search, $search);
$driverStmt->execute();
$drivers = $driverStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------------- RIDES ---------------- */
$rideStmt = $conn->prepare("
    SELECT id, pickup_location, dropoff_location, status
    FROM rides
    WHERE pickup_location LIKE ?
       OR dropoff_location LIKE ?
       OR status LIKE ?
    LIMIT 5
");
$rideStmt->bind_param("sss", $search, $search, $search);
$rideStmt->execute();
$rides = $rideStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------------- RESPONSE ---------------- */
echo json_encode([
    "users" => $users,
    "drivers" => $drivers,
    "rides" => $rides
]);