<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

if (php_sapi_name() === 'cli') {
  parse_str(getenv('QUERY_STRING') ?: '', $_GET);
}

require_once __DIR__ . '/../config/Database.php';

try {
  $db = (new Database())->connect();
  $method = $_SERVER['REQUEST_METHOD'];

  // GET ALL NOTIFICATIONS FOR USER
  if ($method === 'GET') {
    if (!isset($_GET['user_id'])) {
      http_response_code(400);
      echo json_encode(['success' => false, 'message' => 'User ID is required']);
      exit;
    }
    $userId = (int)$_GET['user_id'];

    $stmt = $db->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
      $notifications[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'message' => $row['message'],
        'is_read' => (int)$row['is_read'] === 1,
        'created_at' => $row['created_at']
      ];
    }

    echo json_encode($notifications);
  }
  // MARK AS READ
  elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Check if we need to mark all as read
    if (isset($_GET['read_all']) || (isset($data['read_all']) && $data['read_all'])) {
      if (!isset($_GET['user_id']) && !isset($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required to mark all read']);
        exit;
      }
      $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$data['user_id'];
      
      $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
      $stmt->bind_param('i', $userId);
      if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read']);
      }
    } 
    // Mark specific notification as read
    else {
      $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($data['id']) ? (int)$data['id'] : 0);
      if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
        exit;
      }

      $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
      $stmt->bind_param('i', $id);
      if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
      }
    }
  }
  // DELETE NOTIFICATION
  elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
      exit;
    }

    $stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
    }
  } else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  }
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>
