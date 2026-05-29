<?php

class Admin {

    private $conn;
    private $table = "admins";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ADMIN
    public function getAdmin() {

        $sql = "SELECT id, name, email FROM admins LIMIT 1";

        $result = $this->conn->query($sql);

        return $result->fetch_assoc();
    }

    // UPDATE PROFILE
    public function updateProfile($data) {

        $sql = "
            UPDATE admins
            SET name=?, email=?
            WHERE id=1
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ss",
            $data['name'],
            $data['email']
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