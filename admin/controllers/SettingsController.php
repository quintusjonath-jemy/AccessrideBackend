<?php

include_once "../config/Database.php";
include_once "../models/Settings.php";

class SettingsController {
    private $settings;

    public function __construct() {
        $database = new Database();
        $db = $database->connect();
        $this->settings = new Settings($db);
    }

    public function getSettings($admin_id) {
        $data = $this->settings->getSettingsByAdminId($admin_id);
        if ($data) {
            echo json_encode([
                "success" => true,
                "settings" => $data
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Settings not found for admin ID: " . $admin_id
            ]);
    }

    public function getNotifications($admin_id) {
        $data = $this->settings->getNotifications($admin_id);
        echo json_encode($data);
    }

    public function updateNotifications($admin_id, $data) {
        $success = $this->settings->updateNotifications($admin_id, $data);
        echo json_encode([
            "success" => $success
        ]);
    }

    public function getSystemSettings($admin_id) {
        $data = $this->settings->getSystemSettings($admin_id);
        echo json_encode($data);
    }

    public function updateSystemSettings($admin_id, $data) {
        $success = $this->settings->updateSystemSettings($admin_id, $data);
        echo json_encode([
            "success" => $success
        ]);
    }
}
?>
