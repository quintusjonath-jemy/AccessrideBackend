<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include_once "../controllers/UserController.php";

$controller = new UserController();

$method = $_SERVER['REQUEST_METHOD'];


// GET USERS
if($method === "GET") {

    // Hide user
    if(isset($_GET['hide'])) {

        $controller->hide($_GET['hide']);

    } else {

        $controller->index();
    }
}


// ADD USER
elseif($method === "POST") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $controller->store($data);
}


// UPDATE USER
elseif($method === "PUT") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $controller->update($data);
}


// DELETE USER
elseif($method === "DELETE") {

    if(isset($_GET['id'])) {

        $controller->destroy($_GET['id']);
    }
}

?>