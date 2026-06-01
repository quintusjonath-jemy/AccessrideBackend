<?php

class Admin {

    private $conn;
    private $table = "admins";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ADMIN

    public function getAdmin($id) {

        $stmt = $this->conn->prepare(
            "SELECT * FROM admins WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }

    // UPDATE PROFILE

    public function updateProfile($data) {

        $sql = "
            UPDATE admins
            SET
                name=?,
                email=?,
                phone=?,
                profile_image=?
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssi",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['profile_image'],
            $data['id']
        );

        return $stmt->execute();
    }

    // CHANGE PASSWORD
    public function updatePassword($data) {

        $password = md5($data['new_password']);

        $sql = "
            UPDATE admins
            SET password=?
            WHERE id=1
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "s",
            $password
        );

        return $stmt->execute();
    }

    // GET NOTIFICATION SETTINGS
    public function getNotifications($id) {

        $stmt = $this->conn->prepare("
            SELECT
                sos_alert,
                ride_alert,
                driver_alert,
                email_notifications
            FROM admins
            WHERE id=?
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }


    // UPDATE NOTIFICATION SETTINGS
    public function updateNotifications($data) {

        $stmt = $this->conn->prepare("
            UPDATE admins
            SET
                sos_alert=?,
                ride_alert=?,
                driver_alert=?,
                email_notifications=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "iiiii",
            $data['sos_alert'],
            $data['ride_alert'],
            $data['driver_alert'],
            $data['email_notifications'],
            $data['id']
        );

        return $stmt->execute();
    }
}

?>