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

    if (!$data || empty($data['driver_id']) || empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['phone'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All profile fields are required.'
        ]);
        exit;
    }

    $driverId = (int)$data['driver_id'];
    $firstName = trim($data['first_name']);
    $lastName = trim($data['last_name']);
    $email = trim($data['email']);
    $phone = trim($data['phone']);

    // Basic email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address format.'
        ]);
        exit;
    }

    $database = new Database();
    $db = $database->connect();

    // Verify if email is already taken by another driver
    $checkEmail = $db->prepare("SELECT id FROM drivers WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $driverId);
    $checkEmail->execute();
    $emailExists = $checkEmail->get_result()->fetch_assoc();
    $checkEmail->close();

    if ($emailExists) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'This email address is already registered by another driver.'
        ]);
        exit;
    }

    // Update profile details
    $stmt = $db->prepare("
        UPDATE drivers 
        SET first_name = ?, 
            last_name = ?, 
            email = ?, 
            phone = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $driverId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update profile: ' . $e->getMessage()
    ]);
}
?>
