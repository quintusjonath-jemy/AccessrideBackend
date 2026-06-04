<?php

class User {

    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET USERS
    public function getUsers() {

        $sql = "SELECT * FROM " . $this->table;

        $result = $this->conn->query($sql);

        $users = [];

        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    // ADD USER
    public function addUser($data) {

        $sql = "INSERT INTO users
                (name, email, status, location)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $data['name'],
            $data['email'],
            $data['status'],
            $data['location']
        );

        return $stmt->execute();
    }

    // UPDATE USER
    public function updateUser($data) {

        $sql = "UPDATE users
                SET name=?, email=?, status=?, location=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssi",
            $data['name'],
            $data['email'],
            $data['status'],
            $data['location'],
            $data['id']
        );

        return $stmt->execute();
    }

    // DELETE USER
    public function deleteUser($id) {

        $sql = "DELETE FROM users WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // HIDE USER
    public function toggleUserStatus($id) {

        $stmt = $this->conn->prepare(
            "SELECT status FROM users WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        $newStatus =
            strtolower($user['status']) === 'blocked'
            ? 'active'
            : 'blocked';

        $stmt = $this->conn->prepare(
            "UPDATE users SET status=? WHERE id=?"
        );

        $stmt->bind_param(
            "si",
            $newStatus,
            $id
        );

        return $stmt->execute();
    }
}

?>