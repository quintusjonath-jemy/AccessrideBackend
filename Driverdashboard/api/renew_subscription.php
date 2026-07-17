<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

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
    $amount = isset($data['amount']) ? (float)$data['amount'] : 3000.00;
    
    $database = new Database();
    $db = $database->connect();

    // Start transaction
    $db->begin_transaction();

    // 1. Fetch current subscription details (if any)
    $stmt = $db->prepare("SELECT id, expires_at FROM subscriptions WHERE driver_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $sub = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $today = date('Y-m-d');
    $newExpiry = date('Y-m-d', strtotime('+30 days'));

    if ($sub) {
        $subId = (int)$sub['id'];
        $currentExpiry = $sub['expires_at'];

        if (!empty($currentExpiry) && strtotime($currentExpiry) > time()) {
            $newExpiry = date('Y-m-d', strtotime($currentExpiry . ' +30 days'));
        }

        // Update existing subscription
        $updateStmt = $db->prepare("
            UPDATE subscriptions 
            SET status = 'active', 
                expires_at = ?, 
                last_payment_date = ?, 
                amount = ?, 
                warning_sent = 0,
                updated_at = CURRENT_TIMESTAMP()
            WHERE id = ?
        ");
        $updateStmt->bind_param("ssdi", $newExpiry, $today, $amount, $subId);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        // Create new subscription
        $insertStmt = $db->prepare("
            INSERT INTO subscriptions (driver_id, status, expires_at, last_payment_date, amount, warning_sent) 
            VALUES (?, 'active', ?, ?, ?, 0)
        ");
        $insertStmt->bind_param("issd", $driverId, $newExpiry, $today, $amount);
        $insertStmt->execute();
        $insertStmt->close();
    }

    // 2. Insert transaction history into payments
    $txnId = 'LOCAL_' . strtoupper(uniqid());
    $payMethod = 'local_sandbox';
    $payStatus = 'completed';

    $payStmt = $db->prepare("
        INSERT INTO payments (driver_id, amount, payment_method, status, transaction_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $payStmt->bind_param("idsss", $driverId, $amount, $payMethod, $payStatus, $txnId);
    $payStmt->execute();
    $payStmt->close();

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Subscription renewed successfully.',
        'new_expiry' => $newExpiry,
        'transaction_id' => $txnId
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to renew subscription: ' . $e->getMessage()
    ]);
}
?>
