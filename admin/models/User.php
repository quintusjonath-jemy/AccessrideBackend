<?php

class User {

    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET USERS
    public function getUsers() {
        $sql = "SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, status, location, created_at, phone, profile_image FROM " . $this->table;

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
                (first_name, last_name, email, status, location)
                VALUES (?, ?, ?, ?, ?)";

        $parts = explode(' ', trim($data['name']), 2);
        $first_name = $parts[0];
        $last_name = isset($parts[1]) ? $parts[1] : '';

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssss",
            $first_name,
            $last_name,
            $data['email'],
            $data['status'],
            $data['location']
        );

        return $stmt->execute();
    }

    // UPDATE USER
    public function updateUser($data) {
        $sql = "UPDATE users
                SET first_name=?, last_name=?, email=?, status=?, location=?
                WHERE id=?";

        $parts = explode(' ', trim($data['name']), 2);
        $first_name = $parts[0];
        $last_name = isset($parts[1]) ? $parts[1] : '';

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssi",
            $first_name,
            $last_name,
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