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

    if (!$data || empty($data['driver_id']) || empty($data['card_number']) || empty($data['expiry_date']) || empty($data['cardholder_name'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All card fields are required.'
        ]);
        exit;
    }

    $driverId = (int)$data['driver_id'];
    $holder = trim($data['cardholder_name']);
    $rawCard = preg_replace('/\s+/', '', $data['card_number']);
    $expiry = trim($data['expiry_date']);

    if (strlen($rawCard) < 13 || strlen($rawCard) > 19) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid card number length.'
        ]);
        exit;
    }

    // Determine card brand
    $brand = 'Card';
    if (strpos($rawCard, '4') === 0) {
        $brand = 'Visa';
    } elseif (preg_match('/^5[1-5]/', $rawCard)) {
        $brand = 'Mastercard';
    } elseif (preg_match('/^3[47]/', $rawCard)) {
        $brand = 'AMEX';
    }

    // Mask card (show last 4 digits only)
    $last4 = substr($rawCard, -4);
    $masked = '•••• •••• •••• ' . $last4;

    // Generate simulated authorization token
    $token = 'TOK_CARD_' . strtoupper(md5($driverId . $rawCard . time()));

    $database = new Database();
    $db = $database->connect();

    // Start transaction
    $db->begin_transaction();

    // Reset default status on existing cards
    $resetStmt = $db->prepare("UPDATE driver_cards SET is_default = 0 WHERE driver_id = ?");
    $resetStmt->bind_param("i", $driverId);
    $resetStmt->execute();
    $resetStmt->close();

    // Insert new card as default
    $insertStmt = $db->prepare("
        INSERT INTO driver_cards (driver_id, cardholder_name, card_brand, masked_number, expiry_date, token, is_default) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $insertStmt->bind_param("isssss", $driverId, $holder, $brand, $masked, $expiry, $token);
    $insertStmt->execute();
    $insertStmt->close();

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Card saved successfully.',
        'card' => [
            'card_brand' => $brand,
            'masked_number' => $masked,
            'expiry_date' => $expiry,
            'cardholder_name' => $holder
        ]
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save payment card: ' . $e->getMessage()
    ]);
}
?>
