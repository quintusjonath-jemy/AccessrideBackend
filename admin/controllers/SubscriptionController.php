<?php

include_once "../config/Database.php";
include_once "../models/Subscription.php";

class SubscriptionController {
    private $subscription;

    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->subscription = new Subscription($db);
    }

    // GET ALL SUBSCRIPTIONS OR BY DRIVER ID
    public function index() {
        if (isset($_GET['driver_id'])) {
            $driver_id = (int)$_GET['driver_id'];
            $data = $this->subscription->getSubscriptionByDriverId($driver_id);
            if ($data) {
                echo json_encode([
                    "success" => true,
                    "subscription" => $data
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Subscription not found for driver ID: " . $driver_id
                ]);
            }
        } else {
            $data = $this->subscription->getSubscriptions();
            echo json_encode($data);
        }
    }

    // ADD NEW SUBSCRIPTION
    public function store($data) {
        $success = $this->subscription->addSubscription($data);
        echo json_encode([
            "success" => $success
        ]);
    }

    // UPDATE SUBSCRIPTION
    public function update($data) {
        $success = $this->subscription->updateSubscription($data);
        echo json_encode([
            "success" => $success
        ]);
    }

    // DELETE SUBSCRIPTION
    public function destroy($id) {
        $success = $this->subscription->deleteSubscription($id);
        echo json_encode([
            "success" => $success
        ]);
    }
}
?>
