<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

include_once '../controllers/VehicleController.php';

$controller = new VehicleController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $controller->index();
} elseif ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  $controller->store($data);
} elseif ($method === 'PUT') {
  $data = json_decode(file_get_contents('php://input'), true);
  $controller->update($data);
} elseif ($method === 'DELETE') {
  if (isset($_GET['id'])) {
    $controller->destroy((int) $_GET['id']);
  } else {
    echo json_encode([
      'success' => false,
      'message' => 'Missing vehicle ID parameter'
    ]);
  }
}
?>
