<?php

include_once "../config/Database.php";
include_once "../models/Ride.php";

class RideController {

    private $ride;

    public function __construct() {
        $db = (new Database())->connect();
        $this->ride = new Ride($db);
    }

    public function index() {
        echo json_encode($this->ride->getRides());
    }

    public function store($data) {
        echo json_encode([
            "success" => $this->ride->addRide($data)
        ]);
    }

    public function update($data) {
        echo json_encode([
            "success" => $this->ride->updateRide($data)
        ]);
    }

    public function destroy($id) {
        echo json_encode([
            "success" => $this->ride->deleteRide($id)
        ]);
    }
}
?>