<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include "../config/database.php";

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (is_array($data)) {
    $user_id = $data['id'] ?? $data['user_id'] ?? null;
    $name = $data['name'] ?? null;
    $phone = $data['phone'] ?? null;
    $email = $data['email'] ?? null;
    $location = $data['location'] ?? $data['address'] ?? null;
} else {
    $user_id = $_POST['id'] ?? $_POST['user_id'] ?? null;
    $name = $_POST['name'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $email = $_POST['email'] ?? null;
    $location = $_POST['location'] ?? $_POST['address'] ?? null;
}

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "User ID is required"]);
    exit;
}

$name_parts = explode(' ', trim($name ?? ''), 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';

$sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            phone = ?, 
            email = ?, 
            location = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $first_name, $last_name, $phone, $email, $location, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update profile: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>