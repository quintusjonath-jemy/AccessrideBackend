<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

include_once '../config/Database.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
  echo json_encode([
    'success' => false,
    'message' => 'Database connection failed'
  ]);
  exit;
}

// Ensure warning_sent column exists
$check_column = $conn->query("SHOW COLUMNS FROM subscriptions LIKE 'warning_sent'");
if ($check_column && $check_column->num_rows === 0) {
  $conn->query("ALTER TABLE subscriptions ADD COLUMN warning_sent TINYINT(1) DEFAULT 0");
}

// 0. Automated Subscription Expired Grace Period Warning (3 days)
$threeDaysAgo = date('Y-m-d H:i:s', strtotime('-3 days'));
$warningQuery = "
    SELECT 
        s.driver_id,
        s.expires_at,
        d.email,
        d.phone,
        TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS driver_name
    FROM subscriptions s
    JOIN drivers d ON s.driver_id = d.id
    WHERE s.status = 'expired' 
      AND s.expires_at <= '$threeDaysAgo'
      AND s.warning_sent = 0
";
$warningResult = $conn->query($warningQuery);
if ($warningResult && $warningResult->num_rows > 0) {
  include_once '../config/Encryption.php';
  include_once '../config/SMTPMailer.php';

  // Get SMTP Config
  $smtp_host = '';
  $smtp_port = 465;
  $smtp_user = '';
  $smtp_pass = '';
  $smtp_secure = 'ssl';

  $settingsRes = $conn->query("SELECT * FROM settings WHERE admin_id = 1 LIMIT 1");
  if ($settingsRes && $settingsRes->num_rows > 0) {
    $settingsRow = $settingsRes->fetch_assoc();
    $smtp_host = $settingsRow['smtp_host'] ?? '';
    $smtp_port = intval($settingsRow['smtp_port'] ?? 465);
    $smtp_user = $settingsRow['smtp_user'] ?? '';
    $smtp_pass_enc = $settingsRow['smtp_pass'] ?? '';
    $smtp_secure = $settingsRow['smtp_secure'] ?? 'ssl';
    if (!empty($smtp_pass_enc)) {
      $smtp_pass = Encryption::decrypt($smtp_pass_enc);
    }
  }

  while ($wRow = $warningResult->fetch_assoc()) {
    $driverId = (int) $wRow['driver_id'];
    $driverName = $wRow['driver_name'];
    $driverEmail = $wRow['email'];
    $expiryDate = $wRow['expires_at'];

    // Insert warning notification into admin_notifications
    $adminMsg = "Warning: Driver {$driverName}'s membership subscription expired on {$expiryDate} (over 3 days ago). Automated activation warning sent.";
    $stmtNotif = $conn->prepare("INSERT INTO admin_notifications (type, message) VALUES ('Driver', ?)");
    if ($stmtNotif) {
      $stmtNotif->bind_param('s', $adminMsg);
      $stmtNotif->execute();
    }

    // Send warning email to driver if SMTP config is valid
    if (!empty($smtp_host) && !empty($smtp_user) && !empty($driverEmail)) {
      $mailer = new SMTPMailer($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure);
      $subject = "AccessRide Warning: Please Activate Your Subscription";
      $emailBody = "Dear {$driverName},\n\nYour AccessRide driver membership subscription expired on {$expiryDate} and has not been activated within the 3-day grace period.\n\nPlease log in to your dashboard and activate your subscription to resume receiving ride bookings.\n\nBest regards,\nAccessRide Team";
      $headers = "From: {$smtp_user}\r\nReply-To: {$smtp_user}\r\nContent-Type: text/plain; charset=UTF-8";
      $mailer->send($driverEmail, $subject, $emailBody, $headers);
    }

    // Send warning SMS to driver
    $driverPhone = $wRow['phone'] ?? '';
    if (!empty($driverPhone)) {
      $smsMsg = "AccessRide Notice: Dear {$driverName}, your subscription expired on {$expiryDate}. Please activate it on your dashboard to continue receiving bookings.";
      $logDir = __DIR__ . '/../../logs';
      if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
      }
      file_put_contents($logDir . '/sms_log.txt', "[" . date('Y-m-d H:i:s') . "] SMS Warning sent to {$driverPhone}: {$smsMsg}\n", FILE_APPEND);
    }

    // Mark warning as sent
    $conn->query("UPDATE subscriptions SET warning_sent = 1 WHERE driver_id = $driverId");
  }
}

// 1. Calculate Ride Commissions (Completed Rides)
$ridesQuery = "
    SELECT 
        COALESCE(SUM(fare), 0) AS total_gross_fare,
        COUNT(*) AS total_completed_rides
    FROM rides
    WHERE status = 'completed'
";
$ridesResult = $conn->query($ridesQuery);
$ridesData = $ridesResult->fetch_assoc();
$totalGrossFare = (float) $ridesData['total_gross_fare'];
$totalCompletedRides = (int) $ridesData['total_completed_rides'];

$commissionRate = 0.0;  // No commission
$platformCommission = 0.0;

// 2. Calculate Active Subscription Revenue & Count
$subQuery = "
    SELECT 
        COALESCE(SUM(amount), 0) AS total_sub_earnings,
        COUNT(*) AS active_sub_count
    FROM subscriptions
    WHERE status = 'active'
";
$subResult = $conn->query($subQuery);
$subData = $subResult->fetch_assoc();
$totalSubEarnings = (float) $subData['total_sub_earnings'];
$activeSubCount = (int) $subData['active_sub_count'];

$totalPlatformEarnings = $totalSubEarnings;

// 3. Driver Earnings List
$driversQuery = "
    SELECT 
        d.id,
        TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name,
        v.vehicle_number,
        v.vehicle_type,
        d.phone,
        d.email,
        s.status AS subscription_status,
        s.expires_at AS subscription_expires_at,
        s.amount AS subscription_amount,
        s.warning_sent,
        COUNT(r.id) AS completed_rides_count,
        COALESCE(SUM(r.fare), 0) AS gross_earnings
    FROM drivers d
    LEFT JOIN vehicles v ON d.id = v.driver_id
    LEFT JOIN subscriptions s ON d.id = s.driver_id
    LEFT JOIN rides r ON d.id = r.driver_id AND r.status = 'completed'
    GROUP BY d.id, v.vehicle_number, v.vehicle_type, s.status, s.expires_at, s.amount, s.warning_sent
    ORDER BY gross_earnings DESC
";
$driversResult = $conn->query($driversQuery);
$driversEarnings = [];

if ($driversResult) {
  while ($row = $driversResult->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $row['completed_rides_count'] = (int) $row['completed_rides_count'];
    $row['gross_earnings'] = (float) $row['gross_earnings'];
    $row['commission_deducted'] = 0.0;
    $row['net_earnings'] = $row['gross_earnings'];  // 100% of fare goes to driver
    $row['subscription_amount'] = (float) $row['subscription_amount'];
    $row['warning_sent'] = (int) ($row['warning_sent'] ?? 0);
    $driversEarnings[] = $row;
  }
}

echo json_encode([
  'success' => true,
  'platform' => [
    'total_gross_fare' => $totalGrossFare,
    'commission_rate' => 0,
    'commission_earnings' => 0.0,
    'subscription_earnings' => $totalSubEarnings,
    'active_sub_count' => $activeSubCount,
    'total_earnings' => $totalPlatformEarnings,
    'total_completed_rides' => $totalCompletedRides
  ],
  'drivers' => $driversEarnings
]);
?>
