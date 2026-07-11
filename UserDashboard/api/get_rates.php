<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

require_once __DIR__ . '/../config/Database.php';

try {
  $db = (new Database())->connect();

  // Ensure rate columns exist in settings
  $check_rates = $db->query("SHOW COLUMNS FROM settings LIKE 'rate_bike'");
  if ($check_rates && $check_rates->num_rows === 0) {
    $db->query("ALTER TABLE settings ADD COLUMN rate_bike DECIMAL(10,2) DEFAULT 40.00");
    $db->query("ALTER TABLE settings ADD COLUMN rate_three_wheeler DECIMAL(10,2) DEFAULT 60.00");
    $db->query("ALTER TABLE settings ADD COLUMN rate_car DECIMAL(10,2) DEFAULT 80.00");
    $db->query("ALTER TABLE settings ADD COLUMN rate_van DECIMAL(10,2) DEFAULT 100.00");
  }

  $res = $db->query("SELECT rate_bike, rate_three_wheeler, rate_car, rate_van FROM settings WHERE admin_id = 1 LIMIT 1");
  $rates = [
    'bike' => 40.00,
    'three wheeler' => 60.00,
    'car' => 80.00,
    'van' => 100.00
  ];

  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $rates['bike'] = (float)($row['rate_bike'] ?? 40.00);
    $rates['three wheeler'] = (float)($row['rate_three_wheeler'] ?? 60.00);
    $rates['car'] = (float)($row['rate_car'] ?? 80.00);
    $rates['van'] = (float)($row['rate_van'] ?? 100.00);
  }

  echo json_encode([
    'success' => true,
    'rates' => $rates
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
?>
