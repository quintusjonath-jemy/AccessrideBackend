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

// Base SELECT — per-status counts in one row per date/label
$base_select = "
    SELECT
        label,
        SUM(CASE WHEN status = 'active'    THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
        COUNT(*) AS count
    FROM (
";

if ($filter === 'year') {
    // Group by month within the selected year
    $inner = "
        SELECT
            DATE_FORMAT(ride_date, '%b') AS label,
            MONTH(ride_date) AS sort_key,
            status
        FROM rides
        WHERE YEAR(ride_date) = $year
    ";
    $group = "GROUP BY label, sort_key ORDER BY sort_key ASC";

} elseif ($filter === 'month') {
    // Group by day within the selected year+month
    $inner = "
        SELECT
            DATE_FORMAT(ride_date, '%d %b') AS label,
            DAY(ride_date) AS sort_key,
            status
        FROM rides
        WHERE YEAR(ride_date) = $year AND MONTH(ride_date) = $month
    ";
    $group = "GROUP BY label, sort_key ORDER BY sort_key ASC";

} elseif ($filter === 'week') {
    // Group by day-of-week within the ISO week of the selected year
    $inner = "
        SELECT
            DATE_FORMAT(ride_date, '%a %d') AS label,
            DATE(ride_date) AS sort_key,
            status
        FROM rides
        WHERE YEAR(ride_date) = $year AND WEEK(ride_date, 1) = $week
    ";
    $group = "GROUP BY label, sort_key ORDER BY sort_key ASC";

} else {
    // Fallback: current week
    $inner = "
        SELECT
            DATE_FORMAT(ride_date, '%a %d') AS label,
            DATE(ride_date) AS sort_key,
            status
        FROM rides
        WHERE YEARWEEK(ride_date, 1) = YEARWEEK(CURDATE(), 1)
    ";
    $group = "GROUP BY label, sort_key ORDER BY sort_key ASC";
}

$sql = $base_select . $inner . ") AS sub " . $group;

$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label'     => $row['label'],
            'active'    => (int)$row['active'],
            'pending'   => (int)$row['pending'],
            'completed' => (int)$row['completed'],
            'cancelled' => (int)$row['cancelled'],
            'count'     => (int)$row['count'],
        ];
    }
}

echo json_encode($data);
?>