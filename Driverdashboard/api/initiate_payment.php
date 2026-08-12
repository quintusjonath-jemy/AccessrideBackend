<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

// Self-contained environment loader
function loadEnv() {
    $envPath = __DIR__ . '/../../.env';
    if (!file_exists($envPath)) return;
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match('/^\'(.*)\'$/', $value, $matches)) {
            $value = $matches[1];
        }
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
loadEnv();

try {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || empty($data['driver_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Driver ID is required.'
        ]);
        exit;
    }

    $driverId = (int)$data['driver_id'];
    
    $database = new Database();
    $db = $database->connect();

    // Fetch driver details
    $stmt = $db->prepare("SELECT first_name, last_name, phone, email, town, district FROM drivers WHERE id = ?");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $driver = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$driver) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Driver not found.'
        ]);
        exit;
    }

    // Set plan variables
    $amount = 1500.00; // Standard Subscription Amount (LKR)
    $currency = 'LKR';
    $amount_formatted = number_format($amount, 2, '.', '');
    
    // Generate unique order ID containing driver ID
    $order_id = "SUB_" . $driverId . "_" . time();

    // PayHere parameters from environment variables
    $merchant_id = getenv('PAYHERE_MERCHANT_ID') ?: ($_ENV['PAYHERE_MERCHANT_ID'] ?? '');
    $merchant_secret = getenv('PAYHERE_SECRET') ?: ($_ENV['PAYHERE_SECRET'] ?? '');
    $isSandbox = filter_var(getenv('PAYHERE_SANDBOX') ?: 'true', FILTER_VALIDATE_BOOLEAN);

    // Secure Hash Generation (Standard PayHere Algorithm)
    $hash = strtoupper(
        md5(
            $merchant_id . 
            $order_id . 
            $amount_formatted . 
            $currency . 
            strtoupper(md5($merchant_secret))
        )
    );

    // Fallbacks for profile data
    $first_name = $driver['first_name'] ?: 'Driver';
    $last_name = $driver['last_name'] ?: 'Partner';
    $email = $driver['email'] ?: 'driver@accessride.com';
    $phone = $driver['phone'] ?: '0771234567';
    $address = ($driver['town'] ?: ($driver['district'] ?: 'Colombo')) . ", Sri Lanka";
    $city = $driver['town'] ?: 'Colombo';

    // Application base URLs for PayHere return & IPN callback
    $backendBase = rtrim(getenv('BACKEND_BASE') ?: ($_ENV['BACKEND_BASE'] ?? 'http://localhost'), '/');
    $frontendBase = rtrim(getenv('FRONTEND_BASE') ?: ($_ENV['FRONTEND_BASE'] ?? 'http://localhost:5173'), '/');

    echo json_encode([
        'success' => true,
        'payhere_config' => [
            'sandbox' => $isSandbox,
            'merchant_id' => $merchant_id,
            'return_url' => $frontendBase . '/driver-dashboard',
            'cancel_url' => $frontendBase . '/driver-dashboard',
            'notify_url' => $backendBase . '/Driverdashboard/api/payhere_notify.php',
            'order_id' => $order_id,
            'items' => 'AccessRide Driver Subscription',
            'amount' => $amount_formatted,
            'currency' => $currency,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'country' => 'Sri Lanka',
            'hash' => $hash
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to initiate PayHere session: ' . $e->getMessage()
    ]);
}
?>
