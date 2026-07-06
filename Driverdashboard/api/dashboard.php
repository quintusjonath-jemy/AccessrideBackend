<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/Database.php';
require_once '../controllers/DashboardController.php';

try {
  if (!isset($_GET['driver_id'])) {
    echo json_encode([
      'success' => false,
      'message' => 'Driver ID is required'
    ]);
    exit;
  }

  $driverId = (int) $_GET['driver_id'];

  $database = new Database();
  $db = $database->connect();

  $dashboardController = new DashboardController($db);
  $response = $dashboardController->getDashboardData($driverId);

  echo json_encode($response);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>
