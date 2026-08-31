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

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../../admin/config/Database.php';

try {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request payload']);
        exit;
    }

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing email or password']);
        exit;
    }

    $database = new Database();
    $db = $database->connect();

    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception($db->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    if (!$admin || !password_verify($password, $admin['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Username or password is invalid']);
        exit;
    }

    $_SESSION['admin'] = [
        'id' => $admin['id'],
        'email' => $admin['email'],
        'name' => $admin['name'],
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Admin login successful',
        'admin' => $_SESSION['admin']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
