<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

include_once '../config/Database.php';
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    echo json_encode([]);
    exit;
}

$filter = $_GET['filter'] ?? 'week';
$year   = intval($_GET['year']  ?? date('Y'));
$month  = intval($_GET['month'] ?? date('n'));
$week   = intval($_GET['week']  ?? date('W'));

// Base SQL logic mapping columns
if ($filter === 'year') {
    $labelExpr  = "DATE_FORMAT(created_at, '%b')";
    $rLabelExpr = "DATE_FORMAT(ride_date, '%b')";
    $sortExpr   = "MONTH(created_at)";
    $rSortExpr  = "MONTH(ride_date)";
    $where      = "YEAR(created_at) = $year";
    $rWhere     = "YEAR(ride_date) = $year";
} elseif ($filter === 'month') {
    $labelExpr  = "DATE_FORMAT(created_at, '%d %b')";
    $rLabelExpr = "DATE_FORMAT(ride_date, '%d %b')";
    $sortExpr   = "DAY(created_at)";
    $rSortExpr  = "DAY(ride_date)";
    $where      = "YEAR(created_at) = $year AND MONTH(created_at) = $month";
    $rWhere     = "YEAR(ride_date) = $year AND MONTH(ride_date) = $month";
} elseif ($filter === 'week') {
    $labelExpr  = "DATE_FORMAT(created_at, '%a %d')";
    $rLabelExpr = "DATE_FORMAT(ride_date, '%a %d')";
    $sortExpr   = "DATE(created_at)";
    $rSortExpr  = "DATE(ride_date)";
    $where      = "YEAR(created_at) = $year AND WEEK(created_at, 1) = $week";
    $rWhere     = "YEAR(ride_date) = $year AND WEEK(ride_date, 1) = $week";
} else {
    $labelExpr  = "DATE_FORMAT(created_at, '%a %d')";
    $rLabelExpr = "DATE_FORMAT(ride_date, '%a %d')";
    $sortExpr   = "DATE(created_at)";
    $rSortExpr  = "DATE(ride_date)";
    $where      = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
    $rWhere     = "YEARWEEK(ride_date, 1) = YEARWEEK(CURDATE(), 1)";
}

// 1. Fetch Subscription Earnings
$subSQL = "
    SELECT
        $labelExpr AS label,
        $sortExpr AS sort_key,
        SUM(amount) AS sub_earnings
    FROM subscriptions
    WHERE status = 'active' AND $where
    GROUP BY label, sort_key
";

// 2. Fetch Ride Gross Fare (Gross Volume)
$rideSQL = "
    SELECT
        $rLabelExpr AS label,
        $rSortExpr AS sort_key,
        SUM(fare) AS ride_fare
    FROM rides
    WHERE status = 'completed' AND $rWhere
    GROUP BY label, sort_key
";

$subResult  = $conn->query($subSQL);
$rideResult = $conn->query($rideSQL);

$merged = [];

if ($subResult) {
    while ($row = $subResult->fetch_assoc()) {
        $lbl = $row['label'];
        if (!isset($merged[$lbl])) {
            $merged[$lbl] = ['label' => $lbl, 'sort_key' => $row['sort_key'], 'subscriptions' => 0.0, 'rides' => 0.0];
        }
        $merged[$lbl]['subscriptions'] = (float)$row['sub_earnings'];
    }
}

if ($rideResult) {
    while ($row = $rideResult->fetch_assoc()) {
        $lbl = $row['label'];
        if (!isset($merged[$lbl])) {
            $merged[$lbl] = ['label' => $lbl, 'sort_key' => $row['sort_key'], 'subscriptions' => 0.0, 'rides' => 0.0];
        }
        $merged[$lbl]['rides'] = (float)$row['ride_fare'];
    }
}

// Sort merged results by sort_key
usort($merged, function($a, $b) {
    return strcmp((string)$a['sort_key'], (string)$b['sort_key']);
});

// Format output
$data = array_values($merged);

echo json_encode($data);
?>
