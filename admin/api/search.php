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
    SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, location, phone
    FROM users
    WHERE CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ? 
       OR email LIKE ? 
       OR location LIKE ? 
       OR phone LIKE ?
    LIMIT 5
");
$userStmt->bind_param("ssss", $search, $search, $search, $search);
$userStmt->execute();
$users = $userStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------------- DRIVERS ---------------- */
$driverStmt = $conn->prepare("
    SELECT d.id, TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name, d.email, d.phone, v.vehicle_number, v.vehicle_type
    FROM drivers d
    LEFT JOIN vehicles v ON d.id = v.driver_id
    WHERE CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) LIKE ? 
       OR d.email LIKE ? 
       OR d.phone LIKE ? 
       OR v.vehicle_number LIKE ? 
       OR v.vehicle_type LIKE ?
    LIMIT 5
");
$driverStmt->bind_param("sssss", $search, $search, $search, $search, $search);
$driverStmt->execute();
$drivers = $driverStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------------- RIDES ---------------- */
$rideStmt = $conn->prepare("
    SELECT rides.id, rides.pickup_location, rides.dropoff_location, rides.status
    FROM rides
    LEFT JOIN users ON rides.user_id = users.id
    LEFT JOIN drivers ON rides.driver_id = drivers.id
    LEFT JOIN vehicles ON drivers.id = vehicles.driver_id
    WHERE rides.pickup_location LIKE ?
       OR rides.dropoff_location LIKE ?
       OR rides.status LIKE ?
       OR rides.id LIKE ?
       OR CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) LIKE ?
       OR CONCAT(COALESCE(drivers.first_name, ''), ' ', COALESCE(drivers.last_name, '')) LIKE ?
       OR vehicles.vehicle_number LIKE ?
       OR vehicles.vehicle_type LIKE ?
    LIMIT 5
");
$rideStmt->bind_param("ssssssss", $search, $search, $search, $search, $search, $search, $search, $search);
$rideStmt->execute();
$rides = $rideStmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ---------------- RESPONSE ---------------- */
echo json_encode([
    "users" => $users,
    "drivers" => $drivers,
    "rides" => $rides
]);