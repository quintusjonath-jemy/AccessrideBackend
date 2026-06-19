<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    public function getCurrentUser(): void {
        $currentUser = User::current();
        if ($currentUser) {
            echo json_encode(['user' => $currentUser]);
            return;
        }

        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request payload']);
            return;
        }

        $requiredFields = ['firstName', 'lastName', 'email', 'phone', 'password', 'confirmPassword', 'agree'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required field: ' . $field]);
                return;
            }
        }

        if ($data['password'] !== $data['confirmPassword']) {
            http_response_code(400);
            echo json_encode(['error' => 'Passwords do not match']);
            return;
        }

        $isDriver = !empty($data['isDriver']);
        if (!$isDriver && (empty($data['guardianName']) || empty($data['guardianNumber']))) {
            http_response_code(400);
            echo json_encode(['error' => 'Guardian information is required for riders']);
            return;
        }

        $data['isDriver'] = $isDriver;
        if (!User::save($data)) {
            http_response_code(500);
            echo json_encode(['error' => 'Unable to save registration to database']);
            return;
        }

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Registration saved successfully']);
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request payload']);
            return;
        }

        $isDriver = !empty($data['isDriver']);
        $identifier = $isDriver ? ($data['phone'] ?? null) : ($data['email'] ?? null);
        $password = $data['password'] ?? null;

        if (!$identifier || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing email/phone or password']);
            return;
        }

        // Find user by email (rider) or phone (driver)
        $user = $isDriver ? User::findByPhone($identifier) : User::findByEmail($identifier);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Account not found. Please create an account first.']);
            return;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Incorrect password.']);
            return;
        }

        // Successful login
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'isDriver' => (bool)$user['is_driver'],
        ];

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => $_SESSION['user']]);
    }
}
