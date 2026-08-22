<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../controllers/RideController.php';

$controller = new RideController();

$method = $_SERVER['REQUEST_METHOD'];

// GET RIDES
if ($method === 'GET') {
  $controller->index();
}
// ADD RIDE
elseif ($method === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  $controller->store($data);
}
// UPDATE RIDE
elseif ($method === 'PUT') {
  $data = json_decode(file_get_contents('php://input'), true);
  $controller->update($data);
}
// DELETE RIDE
elseif ($method === 'DELETE') {
  if (isset($_GET['id'])) {
    $controller->destroy($_GET['id']);
  }
}
?>