<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once "../controllers/PaymentController.php";

$controller = new PaymentController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    $controller->index();
} elseif ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $controller->update($data);
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}
?>
