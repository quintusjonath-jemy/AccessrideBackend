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
    $labelExpr = "DATE_FORMAT(created_at, '%b')";
    $sortExpr  = "MONTH(created_at)";
    $where     = "YEAR(created_at) = $year";
} elseif ($filter === 'month') {
    $labelExpr = "DATE_FORMAT(created_at, '%d %b')";
    $sortExpr  = "DAY(created_at)";
    $where     = "YEAR(created_at) = $year AND MONTH(created_at) = $month";
} elseif ($filter === 'week') {
    $labelExpr = "DATE_FORMAT(created_at, '%a %d')";
    $sortExpr  = "DATE(created_at)";
    $where     = "YEAR(created_at) = $year AND WEEK(created_at, 1) = $week";
} else {
    $labelExpr = "DATE_FORMAT(created_at, '%a %d')";
    $sortExpr  = "DATE(created_at)";
    $where     = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
}

$sql = "
    SELECT
        $labelExpr AS label,
        $sortExpr AS sort_key,
        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) AS completed_amount,
        SUM(CASE WHEN status = 'pending'   THEN amount ELSE 0 END) AS pending_amount,
        SUM(CASE WHEN status = 'failed'    THEN amount ELSE 0 END) AS failed_amount,
        SUM(CASE WHEN status = 'refunded'  THEN amount ELSE 0 END) AS refunded_amount,
        COUNT(*) AS tx_count
    FROM payments
    WHERE $where
    GROUP BY label, sort_key
    ORDER BY sort_key ASC
";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label'            => $row['label'],
            'completed_amount' => (float)$row['completed_amount'],
            'pending_amount'   => (float)$row['pending_amount'],
            'failed_amount'    => (float)$row['failed_amount'],
            'refunded_amount'  => (float)$row['refunded_amount'],
            'tx_count'         => (int)$row['tx_count'],
        ];
    }
}

echo json_encode($data);
?>
