<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include "../config/database.php";

$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? 1;

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    
    echo json_encode([
        'id' => (int)$row['id'],
        'name' => $name,
        'phone' => $row['phone'] ?? '',
        'email' => $row['email'] ?? '',
        'location' => $row['location'] ?? ''
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        "error" => "User not found"
    ]);
}

$stmt->close();
$conn->close();
?>