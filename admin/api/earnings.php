<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once "../config/Database.php";

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// 1. Calculate Ride Commissions (Completed Rides)
$ridesQuery = "
    SELECT 
        COALESCE(SUM(fare), 0) AS total_gross_fare,
        COUNT(*) AS total_completed_rides
    FROM rides
    WHERE status = 'completed'
";
$ridesResult = $conn->query($ridesQuery);
$ridesData = $ridesResult->fetch_assoc();
$totalGrossFare = (float)$ridesData['total_gross_fare'];
$totalCompletedRides = (int)$ridesData['total_completed_rides'];

$commissionRate = 0.20; // 20% commission
$platformCommission = $totalGrossFare * $commissionRate;

// 2. Calculate Active Subscription Revenue
$subQuery = "
    SELECT COALESCE(SUM(subscription_amount), 0) AS total_sub_earnings
    FROM drivers
    WHERE subscription_status = 'active'
";
$subResult = $conn->query($subQuery);
$totalSubEarnings = (float)$subResult->fetch_assoc()['total_sub_earnings'];

$totalPlatformEarnings = $platformCommission + $totalSubEarnings;

// 3. Driver Earnings List
$driversQuery = "
    SELECT 
        d.id,
        TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name,
        d.vehicle_number,
        d.vehicle_type,
        d.phone,
        d.subscription_status,
        d.subscription_amount,
        COUNT(r.id) AS completed_rides_count,
        COALESCE(SUM(r.fare), 0) AS gross_earnings
    FROM drivers d
    LEFT JOIN rides r ON d.id = r.driver_id AND r.status = 'completed'
    GROUP BY d.id
    ORDER BY gross_earnings DESC
";
$driversResult = $conn->query($driversQuery);
$driversEarnings = [];

if ($driversResult) {
    while ($row = $driversResult->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['completed_rides_count'] = (int)$row['completed_rides_count'];
        $row['gross_earnings'] = (float)$row['gross_earnings'];
        $row['commission_deducted'] = $row['gross_earnings'] * $commissionRate;
        $row['net_earnings'] = $row['gross_earnings'] * (1 - $commissionRate);
        $row['subscription_amount'] = (float)$row['subscription_amount'];
        $driversEarnings[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "platform" => [
        "total_gross_fare" => $totalGrossFare,
        "commission_rate" => $commissionRate * 100,
        "commission_earnings" => $platformCommission,
        "subscription_earnings" => $totalSubEarnings,
        "total_earnings" => $totalPlatformEarnings,
        "total_completed_rides" => $totalCompletedRides
    ],
    "drivers" => $driversEarnings
]);
?>
