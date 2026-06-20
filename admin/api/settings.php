<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once "../controllers/SettingsController.php";

$controller = new SettingsController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$admin_id = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 1;

if ($method === 'GET') {
    if ($action === 'notifications') {
        $controller->getNotifications($admin_id);
    } elseif ($action === 'system') {
        $controller->getSystemSettings($admin_id);
    } else {
        $controller->getSettings($admin_id);
    }
} 
elseif ($method === 'POST') {
    $data = $_POST;
    if ($action === 'notifications') {
        $controller->updateNotifications($admin_id, $data);
    } elseif ($action === 'system') {
        $controller->updateSystemSettings($admin_id, $data);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
    }
} 
else {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}
?>
