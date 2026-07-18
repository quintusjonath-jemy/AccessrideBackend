<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

try {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || empty($data['driver_id']) || empty($data['card_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Driver ID and Card ID are required.'
        ]);
        exit;
    }

    $driverId = (int)$data['driver_id'];
    $cardId = (int)$data['card_id'];

    $database = new Database();
    $db = $database->connect();

    // Start transaction
    $db->begin_transaction();

    // Check if the card is default before deleting
    $checkStmt = $db->prepare("SELECT is_default FROM driver_cards WHERE id = ? AND driver_id = ?");
    $checkStmt->bind_param("ii", $cardId, $driverId);
    $checkStmt->execute();
    $card = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$card) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Card not found.'
        ]);
        exit;
    }

    $wasDefault = (int)$card['is_default'];

    // Delete the card
    $stmt = $db->prepare("DELETE FROM driver_cards WHERE id = ? AND driver_id = ?");
    $stmt->bind_param("ii", $cardId, $driverId);
    $stmt->execute();
    $stmt->close();

    // If we deleted the default card, set another card as default
    if ($wasDefault) {
        $nextCardStmt = $db->prepare("SELECT id FROM driver_cards WHERE driver_id = ? ORDER BY id DESC LIMIT 1");
        $nextCardStmt->bind_param("i", $driverId);
        $nextCardStmt->execute();
        $next = $nextCardStmt->get_result()->fetch_assoc();
        $nextCardStmt->close();

        if ($next) {
            $nextId = (int)$next['id'];
            $updateStmt = $db->prepare("UPDATE driver_cards SET is_default = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $nextId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Card deleted successfully.'
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete card: ' . $e->getMessage()
    ]);
}
?>
