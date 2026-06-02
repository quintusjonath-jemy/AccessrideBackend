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

        $stmt = $this->conn->prepare(
            "SELECT password
            FROM admins
            WHERE id=?"
        );

        $stmt->bind_param(
            "i",
            $data['id']
        );

        $stmt->execute();

        $admin = $stmt
            ->get_result()
            ->fetch_assoc();

        if (
            !password_verify(
                $data['current_password'],
                $admin['password']
            )
        ) {
            return false;
        }

        $newPassword = password_hash(
            $data['new_password'],
            PASSWORD_DEFAULT
        );

        $stmt = $this->conn->prepare(
            "UPDATE admins
            SET password=?
            WHERE id=?"
        );

        $stmt->bind_param(
            "si",
            $newPassword,
            $data['id']
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