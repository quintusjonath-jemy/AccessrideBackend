<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT");
header("Access-Control-Allow-Headers: Content-Type");

include_once "../controllers/AdminController.php";

$controller = new AdminController();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {

    $controller->index(1);
}

elseif ($method === "PUT") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $controller->updateProfile($data);
}