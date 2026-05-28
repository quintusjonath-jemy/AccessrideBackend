<?php

include_once "../config/Database.php";
include_once "../models/Driver.php";

class DriverController {

    private $driver;

    public function __construct() {
        $db = (new Database())->connect();
        $this->driver = new Driver($db);
    }

    public function index() {
        echo json_encode($this->driver->getDrivers());
    }

    public function show($id) {
        echo json_encode($this->driver->getDriverById($id));
    }

    public function store($data) {
        echo json_encode([
            "success" => $this->driver->addDriver($data)
        ]);
    }

    public function update($data) {
        echo json_encode([
            "success" => $this->driver->updateDriver($data)
        ]);
    }

    public function destroy($id) {
        echo json_encode([
            "success" => $this->driver->deleteDriver($id)
        ]);
    }
}
?>