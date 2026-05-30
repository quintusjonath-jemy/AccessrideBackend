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
   public function index($id) {

        echo json_encode(
            $this->admin->getAdmin($id)
        );
    }

    public function updateProfile($data) {

        echo json_encode([
            "success" =>
            $this->admin->updateProfile($data)
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