<?php

class Admin {

    // UML Class Diagram Attributes (Private)
    private $id;
    private $name;
    private $email;
    private $phone;
    private $profile_image;
    private $password;
    private $created_at;

    private $conn;
    private $table = "admins";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ADMIN
    public function getAdmin($id) {
        $this->id = (int)$id;
        $stmt = $this->conn->prepare(
            "SELECT * FROM admins WHERE id=?"
        );

        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $this->name = $row['name'] ?? null;
            $this->email = $row['email'] ?? null;
            $this->phone = $row['phone'] ?? null;
            $this->profile_image = $row['profile_image'] ?? null;
            $this->password = $row['password'] ?? null;
            $this->created_at = $row['created_at'] ?? null;
        }

        return $row;
    }

    // UPDATE PROFILE
    public function updateProfile($data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->profile_image = $data['profile_image'] ?? '';

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
            $this->name,
            $this->email,
            $this->phone,
            $this->profile_image,
            $this->id
        );

        return $stmt->execute();
    }

    // CHANGE PASSWORD
    public function updatePassword($data) {
        $this->id = (int)($data['id'] ?? 0);

        $stmt = $this->conn->prepare(
            "SELECT password
            FROM admins
            WHERE id=?"
        );

        $stmt->bind_param(
            "i",
            $this->id
        );

        $stmt->execute();

        $admin = $stmt
            ->get_result()
            ->fetch_assoc();

        if (
            !$admin ||
            !password_verify(
                $data['current_password'],
                $admin['password']
            )
        ) {
            return false;
        }

        $this->password = password_hash(
            $data['new_password'],
            PASSWORD_DEFAULT
        );

        $updateStmt = $this->conn->prepare(
            "UPDATE admins
            SET password=?
            WHERE id=?"
        );

        $updateStmt->bind_param(
            "si",
            $this->password,
            $this->id
        );

        return $updateStmt->execute();
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

    public function getSystemSettings($id) {

        $stmt = $this->conn->prepare("
            SELECT
                theme,
                refresh_rate,
                sos_enabled,
                tracking_enabled
            FROM admins
            WHERE id=?
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();
    }

    public function updateSystemSettings($data) {

        $stmt = $this->conn->prepare("
            UPDATE admins
            SET
                theme=?,
                refresh_rate=?,
                sos_enabled=?,
                tracking_enabled=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "siiii",
            $data['theme'],
            $data['refresh_rate'],
            $data['sos_enabled'],
            $data['tracking_enabled'],
            $data['id']
        );

        return $stmt->execute();
    }
}

?>