<?php

require_once __DIR__ . '/../models/Admin.php';

class AdminController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            http_response_code(405);

            echo json_encode([
                "error" => "Method not allowed"
            ]);

            return;
        }

        $body = file_get_contents('php://input');

        $data = json_decode($body, true);

        if (!$data) {

            http_response_code(400);

            echo json_encode([
                "error" => "Invalid request"
            ]);

            return;
        }

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {

            http_response_code(400);

            echo json_encode([
                "error" => "Email and password are required"
            ]);

            return;
        }

        $admin = Admin::findByEmail($email);

        if (!$admin) {

            http_response_code(401);

            echo json_encode([
                "error" => "Admin account not found"
            ]);

            return;
        }

        if (!password_verify($password, $admin['password_hash'])) {

            http_response_code(401);

            echo json_encode([
                "error" => "Incorrect password"
            ]);

            return;
        }

        $_SESSION['admin'] = [

            'id' => $admin['id'],
            'email' => $admin['email']
        ];

        echo json_encode([
            "success" => true,
            "message" => "Admin login successful",
            "admin" => $_SESSION['admin']
        ]);
    }
}