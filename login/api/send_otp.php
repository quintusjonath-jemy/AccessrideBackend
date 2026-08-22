<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../UserDashboard/config/Database.php';
require_once __DIR__ . '/../../admin/config/sms.php';

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $phone = trim($data['phone'] ?? '');

    if (empty($phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Phone number is required.']);
        exit;
    }

    // Clean phone number format
    $cleanPhone = preg_replace('/[\s\-]/', '', $phone);
    
    // Format to E.164 (+94...)
    if (strpos($cleanPhone, '0') === 0) {
        $e164Phone = '+94' . substr($cleanPhone, 1);
    } else if (strpos($cleanPhone, '+') === 0) {
        $e164Phone = $cleanPhone;
    } else {
        $e164Phone = '+94' . $cleanPhone;
    }

    // Generate random 6-digit OTP code
    $otpCode = (string) mt_rand(100000, 999999);

    $db = (new Database())->connect();

    // Invalidate previous pending OTPs for this phone number
    $stmt = $db->prepare("UPDATE driver_otps SET is_verified = 0 WHERE phone = ? OR phone = ?");
    if ($stmt) {
        $stmt->bind_param('ss', $cleanPhone, $e164Phone);
        $stmt->execute();
        $stmt->close();
    }

    // Insert new 6-digit OTP into MySQL (valid for 10 minutes)
    $stmt = $db->prepare("INSERT INTO driver_otps (phone, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error storing OTP.']);
        exit;
    }

    $stmt->bind_param('ss', $cleanPhone, $otpCode);
    $stmt->execute();
    $stmt->close();

    // Dispatch REAL SMS via Gateway Service (Text.lk / Twilio)
    $smsService = new TwilioSMS();
    $message = "Your AccessRide driver verification code is: {$otpCode}. Valid for 10 minutes.";
    $smsResult = $smsService->send($e164Phone, $message);
    $smsSent = ($smsResult === true);

    $isDev = (getenv('APP_ENV') === 'development' || !getenv('APP_ENV'));
    $responsePayload = [
        'success' => true,
        'message' => $smsSent 
            ? "Verification code dispatched to {$e164Phone}." 
            : ($isDev ? "OTP generated ({$otpCode}). Configure TEXTLK_API_TOKEN in .env for live SMS." : "Verification code dispatched."),
        'sms_sent' => $smsSent,
        'phone' => $e164Phone,
    ];

    if ($isDev) {
        $responsePayload['otp'] = $otpCode;
    }

    echo json_encode($responsePayload);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP: ' . $e->getMessage()]);
}
