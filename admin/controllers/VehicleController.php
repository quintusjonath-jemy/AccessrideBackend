<?php

include_once "../config/Database.php";
include_once "../models/Vehicle.php";

class VehicleController {
    private $vehicle;

    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->vehicle = new Vehicle($db);
    }

    // GET ALL VEHICLES OR BY DRIVER ID
    public function index() {
        if (isset($_GET['driver_id'])) {
            $driver_id = (int)$_GET['driver_id'];
            $data = $this->vehicle->getVehicleByDriverId($driver_id);
            if ($data) {
                echo json_encode([
                    "success" => true,
                    "vehicle" => $data
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Vehicle not found for driver ID: " . $driver_id
                ]);
            }
        } else {
            $data = $this->vehicle->getVehicles();
            echo json_encode($data);
        }
    }

    // ADD NEW VEHICLE
    public function store($data) {
        $success = $this->vehicle->addVehicle($data);
        echo json_encode([
            "success" => $success
        ]);
    }

    // UPDATE VEHICLE
    public function update($data) {
        $success = $this->vehicle->updateVehicle($data);
        echo json_encode([
            "success" => $success
        ]);
    }

    // DELETE VEHICLE
    public function destroy($id) {
        $success = $this->vehicle->deleteVehicle($id);
        echo json_encode([
            "success" => $success
        ]);
    }
}
?>
