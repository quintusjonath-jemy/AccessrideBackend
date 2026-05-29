<?php

include_once "../config/Database.php";
include_once "../models/Admin.php";

class AdminController {

    private $admin;

    public function __construct() {

        $database = new Database();

        $db = $database->connect();

        $this->admin = new Admin($db);
    }

    // GET ADMIN
    public function index() {

        echo json_encode(
            $this->admin->getAdmin()
        );
    }

    // UPDATE PROFILE
    public function update($data) {

        $success = $this->admin->updateProfile($data);

        echo json_encode([
            "success" => $success
        ]);
    }

    // UPDATE PASSWORD
    public function password($data) {

        $success = $this->admin->updatePassword($data);

        echo json_encode([
            "success" => $success
        ]);
    }
}

?>