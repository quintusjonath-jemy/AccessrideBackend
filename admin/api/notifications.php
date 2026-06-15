<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include_once "../controllers/NotificationController.php";

$controller = new NotificationController();
$method = $_SERVER['REQUEST_METHOD'];

// GET ALL NOTIFICATIONS
if ($method === "GET") {
    $controller->index();
}

// MARK AS READ (SPECIFIC OR ALL)
elseif ($method === "PUT") {
    if (isset($_GET['id'])) {
        $controller->markAsRead($_GET['id']);
    } elseif (isset($_GET['read_all'])) {
        $controller->markAllAsRead();
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameters id or read_all"]);
    }
}

// DELETE NOTIFICATION
elseif ($method === "DELETE") {
    if (isset($_GET['id'])) {
        $controller->destroy($_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Missing parameter id"]);
    }
}

else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>
