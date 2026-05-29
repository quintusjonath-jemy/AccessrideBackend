<?php

header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Methods: GET, PUT");

header("Access-Control-Allow-Headers: Content-Type");

include_once "../controllers/AdminController.php";

$controller = new AdminController();

$method = $_SERVER['REQUEST_METHOD'];


// GET ADMIN
if($method === "GET") {

    $controller->index();
}


// UPDATE ADMIN
elseif($method === "PUT") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    // CHANGE PASSWORD
    if(isset($data['new_password'])) {

        $controller->password($data);

    } else {

        $controller->update($data);
    }
}

?>