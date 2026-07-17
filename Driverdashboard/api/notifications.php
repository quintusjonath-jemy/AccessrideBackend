<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    // --- MARK AS READ ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        $action = $body['action'] ?? '';
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

    // --- GET NOTIFICATIONS ---
    if (empty($_GET['driver_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Driver ID required']);
        exit;
    }

    $driverId = (int)$_GET['driver_id'];

    // Auto-generate subscription-related notifications if not present
    $subStmt = $db->prepare("
        SELECT status, expires_at FROM subscriptions WHERE driver_id = ? ORDER BY id DESC LIMIT 1
    ");
    $subStmt->bind_param("i", $driverId);
    $subStmt->execute();
    $sub = $subStmt->get_result()->fetch_assoc();
    $subStmt->close();

    if ($sub) {
        $expiresAt = $sub['expires_at'] ?? null;
        $status = $sub['status'] ?? 'expired';

        // If subscription expires within 7 days, create a warning notification (only if not already created today)
        if ($expiresAt && strtotime($expiresAt) > time()) {
            $daysLeft = (int)ceil((strtotime($expiresAt) - time()) / 86400);
            if ($daysLeft <= 7) {
                $checkStmt = $db->prepare("
                    SELECT id FROM driver_notifications 
                    WHERE driver_id = ? AND type = 'warning' AND title LIKE '%Subscription%' AND DATE(created_at) = CURDATE()
                ");
                $checkStmt->bind_param("i", $driverId);
                $checkStmt->execute();
                $exists = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if (!$exists) {
                    $title = "Subscription Expiring Soon";
                    $message = "Your driver subscription expires on {$expiresAt} ({$daysLeft} day(s) left). Renew now to stay active.";
                    $type = 'warning';
                    $insStmt = $db->prepare("INSERT INTO driver_notifications (driver_id, title, message, type) VALUES (?, ?, ?, ?)");
                    $insStmt->bind_param("isss", $driverId, $title, $message, $type);
                    $insStmt->execute();
                    $insStmt->close();
                }
            }
        }

        if ($status === 'active') {
            // Create a payment success notification from last successful payment if not done today
            $payStmt = $db->prepare("
                SELECT amount, payment_method, created_at FROM payments 
                WHERE driver_id = ? AND status = 'completed' ORDER BY id DESC LIMIT 1
            ");
            $payStmt->bind_param("i", $driverId);
            $payStmt->execute();
            $pay = $payStmt->get_result()->fetch_assoc();
            $payStmt->close();

            if ($pay) {
                $checkPay = $db->prepare("
                    SELECT id FROM driver_notifications 
                    WHERE driver_id = ? AND type = 'payment' AND DATE(created_at) = ?
                ");
                $payDate = date('Y-m-d', strtotime($pay['created_at']));
                $checkPay->bind_param("is", $driverId, $payDate);
                $checkPay->execute();
                $payExists = $checkPay->get_result()->fetch_assoc();
                $checkPay->close();

                if (!$payExists) {
                    $title = "Payment Successful";
                    $amount = number_format((float)$pay['amount'], 2);
                    $method = $pay['payment_method'] ?? 'Card';
                    $message = "Your subscription payment of Rs. {$amount} was processed successfully via {$method}. You are now active.";
                    $type = 'payment';
                    $insStmt = $db->prepare("INSERT INTO driver_notifications (driver_id, title, message, type) VALUES (?, ?, ?, ?)");
                    $insStmt->bind_param("isss", $driverId, $title, $message, $type);
                    $insStmt->execute();
                    $insStmt->close();
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
        $row['id'] = (int)$row['id'];
        $row['is_read'] = (int)$row['is_read'];
        $notifications[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => count(array_filter($notifications, fn($n) => $n['is_read'] === 0))
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
