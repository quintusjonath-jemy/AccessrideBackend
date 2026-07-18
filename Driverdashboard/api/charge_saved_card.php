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

try {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || empty($data['driver_id']) || empty($data['card_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Driver ID and Card ID are required.'
        ]);
        exit;
    }

    $driverId = (int)$data['driver_id'];
    $cardId = (int)$data['card_id'];
    $amount = 1500.00; // Updated subscription price

    $database = new Database();
    $db = $database->connect();

    // Start transaction
    $db->begin_transaction();

    // 1. Fetch saved card details
    $cardStmt = $db->prepare("SELECT card_brand, masked_number, token FROM driver_cards WHERE id = ? AND driver_id = ?");
    $cardStmt->bind_param("ii", $cardId, $driverId);
    $cardStmt->execute();
    $card = $cardStmt->get_result()->fetch_assoc();
    $cardStmt->close();

    if (!$card) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Saved card not found.'
        ]);
        exit;
    }

    // 2. Fetch current subscription details (if any)
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

    // 3. Log transaction in payments table
    $last4 = substr($card['masked_number'], -4);
    $payMethod = $card['card_brand'] . ' (' . $last4 . ')';
    $payStatus = 'completed';
    $txnId = '1CLICK_' . strtoupper(uniqid());

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
        'message' => 'Subscription successfully renewed using saved card.',
        'transaction_id' => $txnId,
        'new_expiry' => $newExpiry
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Saved card transaction failed: ' . $e->getMessage()
    ]);
}
?>
