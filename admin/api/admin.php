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

elseif ($method === "POST") {

    $data = $_POST;

    if (isset($_FILES['profile_image'])) {

        $uploadDir = "../uploads/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName =
            time() . "_" .
            basename($_FILES['profile_image']['name']);

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $uploadDir . $fileName
        );

        $data['profile_image'] = $fileName;
    }

    $controller->updateProfile($data);
}