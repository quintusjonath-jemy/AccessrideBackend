<?php
error_reporting(0);
ini_set('display_errors', 0);

// Webhook listener - PayHere calls this in background
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
    // PayHere parameters sent via POST
    $merchant_id = $_POST['merchant_id'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $payhere_amount = $_POST['payhere_amount'] ?? '';
    $payhere_currency = $_POST['payhere_currency'] ?? '';
    $status_code = $_POST['status_code'] ?? '';
    $md5sig = $_POST['md5sig'] ?? '';
    $payment_id = $_POST['payment_id'] ?? '';

    $merchant_secret = getenv('PAYHERE_SECRET') ?: 'Mjg1MzE1MjY5MTI3ODE1NzU2NzAyNDA1Nzc1MTMzMjMwOTQwNzMxMg==';

    // Verify signature
    $local_md5sig = strtoupper(
        md5(
            $merchant_id . 
            $order_id . 
            $payhere_amount . 
            $payhere_currency . 
            $status_code . 
            strtoupper(md5($merchant_secret))
        )
    );

    if ($local_md5sig !== $md5sig) {
        http_response_code(400);
        error_log("PayHere webhook signature validation failed. Local: $local_md5sig, Sent: $md5sig");
        exit("Invalid signature");
    }

    // Process payment if status is successful (status_code = 2)
    if ($status_code == 2) {
        // Parse driver_id from order_id format: "SUB_{driverId}_{timestamp}"
        $parts = explode('_', $order_id);
        if (count($parts) < 3) {
            error_log("PayHere invalid order ID structure: $order_id");
            exit("Invalid order ID");
        }
        $driverId = (int)$parts[1];
        $amount = (float)$payhere_amount;

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
        $payMethod = 'payhere';
        $payStatus = 'completed';

        $payStmt = $db->prepare("
            INSERT INTO payments (driver_id, amount, payment_method, status, transaction_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $payStmt->bind_param("idsss", $driverId, $amount, $payMethod, $payStatus, $payment_id);
        $payStmt->execute();
        $payStmt->close();

        // Commit transaction
        $db->commit();
        echo "Payment successfully processed";
    } else {
        echo "Payment status not success. Status: " . $status_code;
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    error_log("PayHere webhook processing error: " . $e->getMessage());
    echo "Processing Error";
}
?>
