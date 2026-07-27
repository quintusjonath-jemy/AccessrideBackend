<?php
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}




header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../controllers/AlertController.php';

$controller = new AlertController();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// GET ALERTS
if ($method === 'GET') {
  $controller->index();
}
// ADD ALERT
elseif ($method === 'POST') {
  $data = json_decode(
    file_get_contents('php://input'),
    true
  );

  $controller->store($data);
}
// RESOLVE ALERT
elseif ($method === 'PUT') {
  if (isset($_GET['id'])) {
    $controller->resolve($_GET['id']);
  }
}

?>