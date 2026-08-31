<?php

require_once '../config/config.php';
require_once '../models/Driver.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}

$body = file_get_contents('php://input');

$data = json_decode($body, true);

if (!$data) {

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid request"
    ]);

    exit;
}

$phone = trim($data['phone'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($phone) || empty($password)) {

    http_response_code(400);

    echo json_encode([
        "error" => "Phone and password required"
    ]);

    exit;
}

$driver = Driver::login($phone, $password);

if (!$driver) {
    http_response_code(401);
    echo json_encode([
        "error" => "Username or password is invalid"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Driver login successful",
    "driver" => $driver
]);