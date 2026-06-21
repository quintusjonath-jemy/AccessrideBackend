<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/Database.php';

$database = new Database();

$conn = $database->connect();

$filter = $_GET['filter'] ?? 'week';

/* USERS QUERY */

if ($filter === 'day') {
  $userSql = '
        SELECT
            HOUR(created_at) as label,
            COUNT(*) as total_users
        FROM users
        WHERE DATE(created_at)=CURDATE()
        GROUP BY HOUR(created_at)
        ORDER BY label ASC
    ';

  $driverSql = '
        SELECT
            HOUR(created_at) as label,
            COUNT(*) as total_drivers
        FROM drivers
        WHERE DATE(created_at)=CURDATE()
        GROUP BY HOUR(created_at)
        ORDER BY label ASC
    ';
} elseif ($filter === 'month') {
  $userSql = '
        SELECT
            DATE(created_at) as label,
            COUNT(*) as total_users
        FROM users
        WHERE MONTH(created_at)=MONTH(CURDATE())
        GROUP BY DATE(created_at)
        ORDER BY label ASC
    ';

  $driverSql = '
        SELECT
            DATE(created_at) as label,
            COUNT(*) as total_drivers
        FROM drivers
        WHERE MONTH(created_at)=MONTH(CURDATE())
        GROUP BY DATE(created_at)
        ORDER BY label ASC
    ';
} else {
  $userSql = '
        SELECT
            DATE(created_at) as label,
            COUNT(*) as total_users
        FROM users
        WHERE YEARWEEK(created_at, 1)=YEARWEEK(CURDATE(), 1)
        GROUP BY DATE(created_at)
        ORDER BY label ASC
    ';

  $driverSql = '
        SELECT
            DATE(created_at) as label,
            COUNT(*) as total_drivers
        FROM drivers
        WHERE YEARWEEK(created_at, 1)=YEARWEEK(CURDATE(), 1)
        GROUP BY DATE(created_at)
        ORDER BY label ASC
    ';
}

/* USERS */

$userResult = $conn->query($userSql);

$users = [];

while ($row = $userResult->fetch_assoc()) {
  $users[] = $row;
}

/* DRIVERS */

$driverResult = $conn->query($driverSql);

$drivers = [];

while ($row = $driverResult->fetch_assoc()) {
  $drivers[] = $row;
}

echo json_encode([
  'users' => $users,
  'drivers' => $drivers
]);

?>