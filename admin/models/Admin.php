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
                phone=?
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssi",
            $data['name'],
            $data['email'],
            $data['phone'],
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
}

?>