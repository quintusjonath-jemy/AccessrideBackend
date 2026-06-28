<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/Database.php';
require_once '../controllers/DashboardController.php';

try {
  if (!isset($_GET['user_id'])) {
    echo json_encode([
      'success' => false,
      'message' => 'User ID is required'
    ]);

    exit;
  }

  $userId = (int) $_GET['user_id'];

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
