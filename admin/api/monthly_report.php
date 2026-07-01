<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

include_once '../config/Database.php';
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$year  = intval($_GET['year']  ?? date('Y'));
$month = intval($_GET['month'] ?? date('n'));

// Safe query helper — returns false on error, never dies
function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) return false;
    return $result;
}

function fetchVal($conn, $sql, $col = 'cnt') {
    $r = safeQuery($conn, $sql);
    if (!$r) return 0;
    $row = $r->fetch_assoc();
    return $row[$col] ?? 0;
}

// ── 1. Users ──────────────────────────────────────────────────────────────────
$newUsers = (int) fetchVal($conn,
    "SELECT COUNT(*) AS cnt FROM users
     WHERE YEAR(created_at)=$year AND MONTH(created_at)=$month");

$totalUsers = (int) fetchVal($conn,
    "SELECT COUNT(*) AS cnt FROM users
     WHERE (YEAR(created_at)*12 + MONTH(created_at)) <= ($year*12 + $month)");

// ── 2. Drivers ────────────────────────────────────────────────────────────────
$newDrivers = (int) fetchVal($conn,
    "SELECT COUNT(*) AS cnt FROM drivers
     WHERE YEAR(created_at)=$year AND MONTH(created_at)=$month");

$totalDrivers = (int) fetchVal($conn,
    "SELECT COUNT(*) AS cnt FROM drivers
     WHERE (YEAR(created_at)*12 + MONTH(created_at)) <= ($year*12 + $month)");

// ── 3. Rides ──────────────────────────────────────────────────────────────────
$rideSQL = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='completed'  THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status='cancelled'  THEN 1 ELSE 0 END) AS cancelled,
        SUM(CASE WHEN status='pending'    THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='active'     THEN 1 ELSE 0 END) AS active,
        COALESCE(SUM(CASE WHEN status='completed' THEN fare ELSE 0 END), 0) AS total_fare
    FROM rides
    WHERE YEAR(ride_date)=$year AND MONTH(ride_date)=$month
";
$rideResult = safeQuery($conn, $rideSQL);
$rideRow    = $rideResult ? $rideResult->fetch_assoc() : [];

$totalRides     = (int)   ($rideRow['total']      ?? 0);
$completedRides = (int)   ($rideRow['completed']  ?? 0);
$cancelledRides = (int)   ($rideRow['cancelled']  ?? 0);
$pendingRides   = (int)   ($rideRow['pending']    ?? 0);
$activeRides    = (int)   ($rideRow['active']     ?? 0);
$totalFare      = (float) ($rideRow['total_fare'] ?? 0);

$completionRate   = $totalRides > 0 ? round($completedRides / $totalRides * 100, 1) : 0;
$cancellationRate = $totalRides > 0 ? round($cancelledRides / $totalRides * 100, 1) : 0;

// ── 4. Revenue from subscriptions ────────────────────────────────────────────
$subRev = 0.0;
$subCheck = $conn->query("SHOW TABLES LIKE 'subscriptions'");
if ($subCheck && $subCheck->num_rows > 0) {
    $subRev = (float) fetchVal($conn,
        "SELECT COALESCE(SUM(amount),0) AS cnt FROM subscriptions
         WHERE status='active'
           AND YEAR(created_at)=$year AND MONTH(created_at)=$month", 'cnt');
}

// ── 5. Top drivers this month ─────────────────────────────────────────────────
$topDrivers = [];
$tdSQL = "
    SELECT
        TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) AS name,
        d.phone,
        COUNT(r.id) AS rides_completed,
        COALESCE(SUM(r.fare), 0) AS earnings
    FROM drivers d
    INNER JOIN rides r ON r.driver_id = d.id
        AND r.status = 'completed'
        AND YEAR(r.ride_date) = $year
        AND MONTH(r.ride_date) = $month
    GROUP BY d.id, d.first_name, d.last_name, d.phone
    ORDER BY rides_completed DESC
    LIMIT 10
";
$tdResult = safeQuery($conn, $tdSQL);
if ($tdResult) {
    while ($row = $tdResult->fetch_assoc()) {
        $topDrivers[] = [
            'name'            => trim($row['name']),
            'phone'           => $row['phone'] ?? '—',
            'rides_completed' => (int)   $row['rides_completed'],
            'earnings'        => (float) $row['earnings'],
        ];
    }
}

// ── 6. Alerts ─────────────────────────────────────────────────────────────────
$alertStats = ['total'=>0,'sos'=>0,'low_battery'=>0,'navigation'=>0,'driver_emergency'=>0,'resolved'=>0];

// Check if alerts table exists
$aCheck = $conn->query("SHOW TABLES LIKE 'alerts'");
if ($aCheck && $aCheck->num_rows > 0) {
    $aSQL = "
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN alert_type='sos'              THEN 1 ELSE 0 END) AS sos,
            SUM(CASE WHEN alert_type='low_battery'      THEN 1 ELSE 0 END) AS low_battery,
            SUM(CASE WHEN alert_type='navigation'       THEN 1 ELSE 0 END) AS navigation,
            SUM(CASE WHEN alert_type='driver_emergency' THEN 1 ELSE 0 END) AS driver_emergency,
            SUM(CASE WHEN status='resolved'             THEN 1 ELSE 0 END) AS resolved
        FROM alerts
        WHERE YEAR(created_at)=$year AND MONTH(created_at)=$month
    ";
    $aResult = safeQuery($conn, $aSQL);
    if ($aResult) {
        $aRow = $aResult->fetch_assoc();
        $alertStats = [
            'total'            => (int) $aRow['total'],
            'sos'              => (int) $aRow['sos'],
            'low_battery'      => (int) $aRow['low_battery'],
            'navigation'       => (int) $aRow['navigation'],
            'driver_emergency' => (int) $aRow['driver_emergency'],
            'resolved'         => (int) $aRow['resolved'],
        ];
    }
}

// ── 7. Month name ─────────────────────────────────────────────────────────────
$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

// ── Response ──────────────────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'period'  => ['month' => $month, 'year' => $year, 'month_name' => $monthName],
    'users'   => ['new' => $newUsers,   'total' => $totalUsers],
    'drivers' => ['new' => $newDrivers, 'total' => $totalDrivers, 'removed' => 0],
    'rides'   => [
        'total'             => $totalRides,
        'completed'         => $completedRides,
        'cancelled'         => $cancelledRides,
        'pending'           => $pendingRides,
        'active'            => $activeRides,
        'completion_rate'   => $completionRate,
        'cancellation_rate' => $cancellationRate,
        'total_fare'        => $totalFare,
    ],
    'revenue' => [
        'ride_fare'     => $totalFare,
        'subscriptions' => $subRev,
        'total'         => $totalFare + $subRev,
    ],
    'top_drivers' => $topDrivers,
    'alerts'      => $alertStats,
]);
?>
