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
}
