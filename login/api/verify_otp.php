<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../UserDashboard/config/Database.php';

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $phone = trim($data['phone'] ?? '');
    $otp = trim($data['otp'] ?? '');

    if (empty($phone) || empty($otp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Phone number and 6-digit OTP code are required.']);
        exit;
    }

    $cleanPhone = preg_replace('/[\s\-]/', '', $phone);
    if (strpos($cleanPhone, '0') === 0) {
        $e164Phone = '+94' . substr($cleanPhone, 1);
    } else if (strpos($cleanPhone, '+') === 0) {
        $e164Phone = $cleanPhone;
    } else {
        $e164Phone = '+94' . $cleanPhone;
    }

    $db = (new Database())->connect();

    // Query for active matching OTP in MySQL database
    $stmt = $db->prepare("
        SELECT id FROM driver_otps 
        WHERE (phone = ? OR phone = ?) 
          AND otp_code = ? 
          AND expires_at >= NOW() 
        ORDER BY id DESC LIMIT 1
    ");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error verifying OTP.']);
        exit;
    }

    $stmt->bind_param('sss', $cleanPhone, $e164Phone, $otp);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        // Mark OTP as verified
        $updateStmt = $db->prepare("UPDATE driver_otps SET is_verified = 1 WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param('i', $row['id']);
            $updateStmt->execute();
            $updateStmt->close();
        }

        echo json_encode(['success' => true, 'message' => 'Phone number verified successfully!']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired 6-digit OTP code.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Verification error: ' . $e->getMessage()]);
}
