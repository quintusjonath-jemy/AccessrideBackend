<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}



header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../../utils/IdHelper.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = IdHelper::decodeUser($data['user_id'] ?? null);

    if (!$userId || !isset($data['location'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID and location are required'
        ]);
        exit;
    }

    $location = trim($data['location']);

    $database = new Database();
    $db = $database->connect();

    $stmt = $db->prepare('UPDATE users SET location = ? WHERE id = ?');
    if (!$stmt) {
        throw new Exception($db->error);
    }
    $stmt->bind_param('si', $location, $userId);
    $success = $stmt->execute();
    $stmt->close();
    $db->close();

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Location updated successfully' : 'Failed to update location'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
