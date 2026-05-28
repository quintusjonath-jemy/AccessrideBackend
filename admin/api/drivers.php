<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include_once "../controllers/DriverController.php";

$controller = new DriverController();

$method = $_SERVER['REQUEST_METHOD'];

// GET DRIVERS
if ($method === "GET") {

    if (isset($_GET['id'])) {
        $controller->show($_GET['id']);
    } else {
        $controller->index();
    }
}

// ADD DRIVER
elseif ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);
    $controller->store($data);
}

// UPDATE DRIVER
elseif ($method === "PUT") {

    $data = json_decode(file_get_contents("php://input"), true);
    $controller->update($data);
}

// DELETE DRIVER
elseif ($method === "DELETE") {

    if (isset($_GET['id'])) {
        $controller->destroy($_GET['id']);
    }
}

// UPDATE LOCATION
if(isset($data['latitude'])) {

    $controller->updateLocation($data);

} else {

    $controller->update($data);
}
?>