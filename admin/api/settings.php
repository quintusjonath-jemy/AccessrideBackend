<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once "../controllers/SettingsController.php";

$controller = new SettingsController();
$method = $_SERVER['REQUEST_METHOD'];

$admin_id = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 1;

if ($method === 'GET') {
    $controller->getSettings($admin_id);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}
?>
