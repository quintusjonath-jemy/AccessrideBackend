<?php

require_once '../config/config.php';
require_once '../models/Driver.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        "error" => "Method not allowed"
    ]);

    exit;
}

try {
    $data = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);
    $files = $_FILES ?? [];
    $result = Driver::register($data, $files);

    if ($result) {
        echo json_encode([
            "success" => true,
            "message" => "Driver registration submitted successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "error" => "Driver registration failed"
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}