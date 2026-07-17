<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

try {
    if (empty($_GET['driver_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Driver ID is required.'
        ]);
        exit;
    }

    $driverId = (int)$_GET['driver_id'];

    $database = new Database();
    $db = $database->connect();

    $stmt = $db->prepare("
        SELECT id, cardholder_name, card_brand, masked_number, expiry_date, token, is_default, created_at 
        FROM driver_cards 
        WHERE driver_id = ? 
        ORDER BY is_default DESC, id DESC
    ");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cards = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['is_default'] = (int)$row['is_default'];
        $cards[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'cards' => $cards
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch saved cards: ' . $e->getMessage()
    ]);
}
?>
