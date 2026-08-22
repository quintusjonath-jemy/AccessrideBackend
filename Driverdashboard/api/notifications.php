<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    // Create notifications table if not exists
    $db->query("
        CREATE TABLE IF NOT EXISTS driver_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id INT NOT NULL,
            title VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('info','success','warning','payment','ride','system') DEFAULT 'info',
            is_read TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (driver_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // -------------------------------------------------------
    // POST: mark as read / mark all read
    // -------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        $action   = $body['action'] ?? '';
        $driverId = isset($body['driver_id']) ? (int)$body['driver_id'] : 0;

        if (!$driverId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Driver ID required']);
            exit;
        }

        if ($action === 'mark_read') {
            $notifId = isset($body['notification_id']) ? (int)$body['notification_id'] : 0;
            if ($notifId) {
                $stmt = $db->prepare("UPDATE driver_notifications SET is_read = 1 WHERE id = ? AND driver_id = ?");
                $stmt->bind_param("ii", $notifId, $driverId);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($action === 'mark_all_read') {
            $stmt = $db->prepare("UPDATE driver_notifications SET is_read = 1 WHERE driver_id = ?");
            $stmt->bind_param("i", $driverId);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // -------------------------------------------------------
    // GET: fetch notifications
    // -------------------------------------------------------
    if (empty($_GET['driver_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Driver ID required']);
        exit;
    }

    $driverId = (int)$_GET['driver_id'];

    // -----------------------------------------------------------
    // COUNT-ONLY mode: DriverHeader bell uses ?driver_id=X&count=1
    // This does NOT auto-generate any notifications — it just
    // returns the unread count quickly so the bell badge updates.
    // -----------------------------------------------------------
    if (!empty($_GET['count'])) {
        $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM driver_notifications WHERE driver_id = ? AND is_read = 0");
        $countStmt->bind_param("i", $driverId);
        $countStmt->execute();
        $countRow = $countStmt->get_result()->fetch_assoc();
        $countStmt->close();
        echo json_encode(['success' => true, 'unread_count' => (int)($countRow['cnt'] ?? 0)]);
        exit;
    }

    // -----------------------------------------------------------
    // Full GET: auto-generate system notifications then return list
    // -----------------------------------------------------------

    // 1. Subscription expiry warning
    // Insert at most one warning per calendar day per driver.
    $subStmt = $db->prepare("
        SELECT status, expires_at FROM subscriptions
        WHERE driver_id = ? ORDER BY id DESC LIMIT 1
    ");
    $subStmt->bind_param("i", $driverId);
    $subStmt->execute();
    $sub = $subStmt->get_result()->fetch_assoc();
    $subStmt->close();

    if ($sub) {
        $expiresAt = $sub['expires_at'] ?? null;
        $status    = $sub['status'] ?? 'expired';

        if ($expiresAt && strtotime($expiresAt) > time()) {
            $daysLeft = (int)ceil((strtotime($expiresAt) - time()) / 86400);
            if ($daysLeft <= 7) {
                // Check: one warning per day
                $checkStmt = $db->prepare("
                    SELECT id FROM driver_notifications
                    WHERE driver_id = ?
                      AND type = 'warning'
                      AND title = 'Subscription Expiring Soon'
                      AND DATE(created_at) = CURDATE()
                    LIMIT 1
                ");
                $checkStmt->bind_param("i", $driverId);
                $checkStmt->execute();
                $exists = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if (!$exists) {
                    $title   = "Subscription Expiring Soon";
                    $message = "Your driver subscription expires on {$expiresAt} ({$daysLeft} day(s) left). Renew now to stay active.";
                    $type    = 'warning';
                    $ins     = $db->prepare("INSERT INTO driver_notifications (driver_id, title, message, type) VALUES (?, ?, ?, ?)");
                    $ins->bind_param("isss", $driverId, $title, $message, $type);
                    $ins->execute();
                    $ins->close();
                }
            }
        }

        // 2. Payment success notification
        // FIX: key by the exact payment ID, not just its date.
        // We store the payment ID in the message so we can check if we
        // already created a notification for that specific payment.
        if ($status === 'active') {
            $payStmt = $db->prepare("
                SELECT id, amount, payment_method, created_at FROM payments
                WHERE driver_id = ? AND status = 'completed'
                ORDER BY id DESC LIMIT 1
            ");
            $payStmt->bind_param("i", $driverId);
            $payStmt->execute();
            $pay = $payStmt->get_result()->fetch_assoc();
            $payStmt->close();

            if ($pay) {
                $paymentId = (int)$pay['id'];

                // Check: has a payment notification already been created for this exact payment?
                $checkPay = $db->prepare("
                    SELECT id FROM driver_notifications
                    WHERE driver_id = ?
                      AND type = 'payment'
                      AND message LIKE ?
                    LIMIT 1
                ");
                $searchPattern = "%#PAY-{$paymentId}%";
                $checkPay->bind_param("is", $driverId, $searchPattern);
                $checkPay->execute();
                $payExists = $checkPay->get_result()->fetch_assoc();
                $checkPay->close();

                if (!$payExists) {
                    $title   = "Payment Successful";
                    $amount  = number_format((float)$pay['amount'], 2);
                    $method  = $pay['payment_method'] ?? 'Card';
                    $message = "Your subscription payment of Rs. {$amount} was processed successfully via {$method}. You are now active. [#PAY-{$paymentId}]";
                    $type    = 'payment';
                    $ins     = $db->prepare("INSERT INTO driver_notifications (driver_id, title, message, type) VALUES (?, ?, ?, ?)");
                    $ins->bind_param("isss", $driverId, $title, $message, $type);
                    $ins->execute();
                    $ins->close();
                }
            }
        }
    }

    // Fetch all notifications for driver
    $stmt = $db->prepare("
        SELECT id, title, message, type, is_read, created_at
        FROM driver_notifications
        WHERE driver_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        // Strip the internal payment ID tag from the displayed message
        $row['message'] = preg_replace('/\s*\[#PAY-\d+\]/', '', $row['message']);
        $row['id']       = (int)$row['id'];
        $row['is_read']  = (int)$row['is_read'];
        $notifications[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success'      => true,
        'notifications' => $notifications,
        'unread_count' => count(array_filter($notifications, fn($n) => $n['is_read'] === 0))
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
