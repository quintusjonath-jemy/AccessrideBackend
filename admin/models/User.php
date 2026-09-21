<?php
require_once __DIR__ . '/../../utils/IdHelper.php';

class User {

    // UML Class Diagram Attributes (Private)
    private $id;
    private $first_name;
    private $last_name;
    private $email;
    private $phone;
    private $profile_image;
    private $status;
    private $location;
    private $created_at;

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
        $parts = explode(' ', trim($data['name'] ?? ''), 2);
        $first_name = $parts[0] ?? '';
        $last_name = isset($parts[1]) ? $parts[1] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $phone = isset($data['phone']) ? $data['phone'] : '';
        $status = isset($data['status']) ? $data['status'] : 'active';
        $location = isset($data['location']) ? $data['location'] : '';
        $password_hash = password_hash($data['password'] ?? 'User@123', PASSWORD_BCRYPT);

        $sql = "INSERT INTO users
                (first_name, last_name, email, phone, status, location, password_hash)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssss",
            $first_name,
            $last_name,
            $email,
            $phone,
            $status,
            $location,
            $password_hash
        );

        return $stmt->execute();
    }

    // UPDATE USER
    public function updateUser($data) {
        $userId = IdHelper::decodeUser($data['id'] ?? null);
        if (!$userId) return false;

        $oldRes = $this->conn->query("SELECT * FROM users WHERE id = " . (int)$userId);
        if (!$oldRes || $oldRes->num_rows === 0) return false;
        $oldRow = $oldRes->fetch_assoc();

        if (isset($data['name'])) {
            $parts = explode(' ', trim($data['name']), 2);
            $first_name = $parts[0];
            $last_name = isset($parts[1]) ? $parts[1] : '';
        } else {
            $first_name = $oldRow['first_name'];
            $last_name = $oldRow['last_name'];
        }

        $email = $data['email'] ?? $oldRow['email'];
        $phone = $data['phone'] ?? $oldRow['phone'];
        $status = $data['status'] ?? $oldRow['status'];
        $location = $data['location'] ?? $oldRow['location'];

        $sql = "UPDATE users
                SET first_name=?, last_name=?, email=?, phone=?, status=?, location=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssssi",
            $first_name,
            $last_name,
            $email,
            $phone,
            $status,
            $location,
            $userId
        );

        return $stmt->execute();
    }

    // DELETE USER
    public function deleteUser($id) {
        $userId = IdHelper::decodeUser($id);
        $sql = "DELETE FROM users WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $userId);

        return $stmt->execute();
    }

    // HIDE USER
    public function toggleUserStatus($id) {
        $userId = IdHelper::decodeUser($id);
        $stmt = $this->conn->prepare(
            "SELECT status FROM users WHERE id=?"
        );

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) return false;

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
            $userId
        );

        return $stmt->execute();
    }

    public function getUserById($id) {
        $userId = IdHelper::decodeUser($id);
        $stmt = $this->conn->prepare("
            SELECT 
                u.id, 
                u.first_name, 
                u.last_name, 
                TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) AS name,
                u.email, 
                u.phone, 
                u.status, 
                u.location, 
                u.created_at,
                (SELECT contact_name FROM emergency_contacts ec WHERE ec.user_id = u.id ORDER BY ec.id DESC LIMIT 1) AS contact_name,
                (SELECT phone_number FROM emergency_contacts ec WHERE ec.user_id = u.id ORDER BY ec.id DESC LIMIT 1) AS contact_phone,
                (SELECT COUNT(*) FROM rides r WHERE r.user_id = u.id AND r.status = 'completed') AS completed_rides_count,
                (SELECT COALESCE(SUM(r.fare), 0) FROM rides r WHERE r.user_id = u.id AND r.status = 'completed') AS total_amount_paid
            FROM users u
            WHERE u.id=?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $result['completed_rides_count'] = (int) $result['completed_rides_count'];
            $result['total_amount_paid'] = (float) $result['total_amount_paid'];
        }
        return $result;
    }
}

?>