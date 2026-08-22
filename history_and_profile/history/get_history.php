<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include "../config/database.php";

$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? 1;

$sql = "SELECT 
            r.id,
            r.user_id,
            r.driver_id,
            r.pickup_location,
            r.dropoff_location,
            r.ride_date,
            r.status,
            r.fare,
            r.distance_km,
            r.payment_method,
            r.vehicle_type,
            r.rating,
            d.first_name AS driver_first_name,
            d.last_name AS driver_last_name,
            v.vehicle_number,
            v.vehicle_type AS vehicle_class
        FROM rides r
        LEFT JOIN drivers d ON r.driver_id = d.id
        LEFT JOIN vehicles v ON d.id = v.driver_id
        WHERE r.user_id = ?
        ORDER BY r.ride_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$rides = [];

function formatDateSection($dateStr) {
    $timestamp = strtotime($dateStr);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');
    
    $rideDate = strtotime(date('Y-m-d', $timestamp));
    
    if ($rideDate == $today) {
        return "Today, " . date("F d", $timestamp);
    } elseif ($rideDate == $yesterday) {
        return "Yesterday, " . date("F d", $timestamp);
    } else {
        return date("F d, Y", $timestamp);
    }
}

function getDateBadgeColor($dateStr) {
    $timestamp = strtotime($dateStr);
    $today = strtotime('today');
    $rideDate = strtotime(date('Y-m-d', $timestamp));
    return ($rideDate == $today) ? "bg-[#ffb703]" : "bg-slate-300";
}

function getStatusClasses($status) {
    $status = strtolower($status);
    if ($status === 'completed') {
        return [
            'bgColorClass' => 'bg-gradient-to-br from-green-50 to-transparent',
            'statusBadgeClass' => 'bg-green-50 text-green-700 border border-green-200'
        ];
    } elseif ($status === 'cancelled' || $status === 'emergency') {
        return [
            'bgColorClass' => 'bg-gradient-to-br from-red-50 to-transparent',
            'statusBadgeClass' => 'bg-red-50 text-red-700 border border-red-200'
        ];
    } else {
        return [
            'bgColorClass' => 'bg-gradient-to-br from-yellow-50 to-transparent',
            'statusBadgeClass' => 'bg-yellow-50 text-yellow-700 border border-yellow-200'
        ];
    }
}

while ($row = $result->fetch_assoc()) {
    $classes = getStatusClasses($row['status']);
    
    $driverName = null;
    $driverInitial = null;
    if (!empty($row['driver_first_name'])) {
        $lastInitial = !empty($row['driver_last_name']) ? ' ' . substr($row['driver_last_name'], 0, 1) . '.' : '';
        $driverName = $row['driver_first_name'] . $lastInitial;
        $driverInitial = substr($row['driver_first_name'], 0, 1);
    }

    $price = "Rs. " . number_format($row['fare'] ?? 0, 2);
    if (strtolower($row['status']) === 'cancelled') {
        $price = "Rs. 0.00";
    }

    $rides[] = [
        'id' => (int)$row['id'],
        'dateSection' => formatDateSection($row['ride_date']),
        'dateBadgeColor' => getDateBadgeColor($row['ride_date']),
        'status' => ucfirst($row['status']),
        'time' => date("h:i A", strtotime($row['ride_date'])),
        'driverName' => $driverName,
        'driverInitial' => $driverInitial,
        'accessible' => (strpos(strtolower($row['vehicle_class'] ?? ''), 'wheelchair') !== false || strpos(strtolower($row['vehicle_class'] ?? ''), 'accessible') !== false),
        'vehicle' => $row['vehicle_class'] ?? null,
        'licensePlate' => $row['vehicle_number'] ?? null,
        'startLocation' => $row['pickup_location'],
        'endLocation' => $row['dropoff_location'],
        'price' => $price,
        'bgColorClass' => $classes['bgColorClass'],
        'statusBadgeClass' => $classes['statusBadgeClass']
    ];
}

echo json_encode($rides);

$stmt->close();
$conn->close();
?>