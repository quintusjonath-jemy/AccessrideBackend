<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../../utils/IdHelper.php';

try {
  $userId = IdHelper::decodeUser($_GET['user_id'] ?? null);
  if (!$userId) {
    echo json_encode([
      'success' => false,
      'message' => 'User ID is required'
    ]);
    exit;
  }

  $database = new Database();
  $db = $database->connect();

  $dashboardController = new DashboardController($db);

  $response =
    $dashboardController->getDashboardData($userId);

  echo json_encode($response);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
