<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../config/Database.php';

$database = new Database();
$conn = $database->connect();

$filter = $_GET['filter'] ?? 'week';
$year   = intval($_GET['year']  ?? date('Y'));
$month  = intval($_GET['month'] ?? date('n'));
$week   = intval($_GET['week']  ?? date('W'));

// ── Build WHERE + GROUP based on filter mode ──────────────────────────────────

if ($filter === 'year') {
    // Each data point = one month of the selected year
    $labelExpr  = "DATE_FORMAT(created_at, '%b')";   // "Jan", "Feb", …
    $sortExpr   = "MONTH(created_at)";
    $where      = "YEAR(created_at) = $year";

} elseif ($filter === 'month') {
    // Each data point = one day of the selected month+year
    $labelExpr  = "DATE_FORMAT(created_at, '%d %b')"; // "01 Jun", "02 Jun", …
    $sortExpr   = "DAY(created_at)";
    $where      = "YEAR(created_at) = $year AND MONTH(created_at) = $month";

} elseif ($filter === 'week') {
    // Each data point = one day of the selected ISO week in the selected year
    $labelExpr  = "DATE_FORMAT(created_at, '%a %d')"; // "Mon 23", …
    $sortExpr   = "DATE(created_at)";
    $where      = "YEAR(created_at) = $year AND WEEK(created_at, 1) = $week";

} else {
    // Fallback — current ISO week
    $labelExpr  = "DATE_FORMAT(created_at, '%a %d')";
    $sortExpr   = "DATE(created_at)";
    $where      = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
}

// ── Users query ───────────────────────────────────────────────────────────────
$userSql = "
    SELECT
        $labelExpr  AS label,
        $sortExpr   AS sort_key,
        COUNT(*)    AS total_users
    FROM users
    WHERE $where
    GROUP BY label, sort_key
    ORDER BY sort_key ASC
";

// ── Drivers query ─────────────────────────────────────────────────────────────
$driverSql = "
    SELECT
        $labelExpr  AS label,
        $sortExpr   AS sort_key,
        COUNT(*)    AS total_drivers
    FROM drivers
    WHERE $where
    GROUP BY label, sort_key
    ORDER BY sort_key ASC
";

// ── Execute ───────────────────────────────────────────────────────────────────
$userResult   = $conn->query($userSql);
$driverResult = $conn->query($driverSql);

$users   = [];
$drivers = [];

if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = [
            'label'       => $row['label'],
            'total_users' => (int)$row['total_users'],
        ];
    }
}

if ($driverResult) {
    while ($row = $driverResult->fetch_assoc()) {
        $drivers[] = [
            'label'         => $row['label'],
            'total_drivers' => (int)$row['total_drivers'],
        ];
    }
}

echo json_encode([
    'users'   => $users,
    'drivers' => $drivers,
]);
?>