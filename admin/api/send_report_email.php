<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

include_once '../config/Database.php';
include_once '../config/SMTPMailer.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$year  = intval($input['year'] ?? date('Y'));
$month = intval($input['month'] ?? date('n'));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Retrieve SMTP settings from DB (admin_id = 1)
$smtpQuery = $conn->query("SELECT smtp_host, smtp_port, smtp_user, smtp_pass, smtp_secure FROM settings WHERE admin_id = 1 LIMIT 1");
$smtpSettings = $smtpQuery ? $smtpQuery->fetch_assoc() : null;

// ── 1. Gather all statistics for the report ───────────────────────────────────
$where = "YEAR(created_at) = $year AND MONTH(created_at) = $month";
$rWhere = "YEAR(ride_date)  = $year AND MONTH(ride_date)  = $month";

$newUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE $where")->fetch_assoc()['cnt'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE YEAR(created_at)*12+MONTH(created_at) <= $year*12+$month")->fetch_assoc()['cnt'] ?? 0;
$newDrivers = $conn->query("SELECT COUNT(*) AS cnt FROM drivers WHERE $where")->fetch_assoc()['cnt'] ?? 0;
$totalDrivers = $conn->query("SELECT COUNT(*) AS cnt FROM drivers WHERE YEAR(created_at)*12+MONTH(created_at) <= $year*12+$month")->fetch_assoc()['cnt'] ?? 0;

$rideStats = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled,
        SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='active'    THEN 1 ELSE 0 END) AS active,
        COALESCE(SUM(CASE WHEN status='completed' THEN fare ELSE 0 END), 0) AS total_fare
    FROM rides
    WHERE $rWhere
")->fetch_assoc();

$totalRides     = (int)   ($rideStats['total'] ?? 0);
$completedRides = (int)   ($rideStats['completed'] ?? 0);
$cancelledRides = (int)   ($rideStats['cancelled'] ?? 0);
$pendingRides   = (int)   ($rideStats['pending'] ?? 0);
$activeRides    = (int)   ($rideStats['active'] ?? 0);
$totalFare      = (float) ($rideStats['total_fare'] ?? 0);

$completionRate  = $totalRides > 0 ? round($completedRides / $totalRides * 100, 1) : 0;
$cancellationRate = $totalRides > 0 ? round($cancelledRides / $totalRides * 100, 1) : 0;

$subRev = 0.0;
$subCheck = $conn->query("SHOW TABLES LIKE 'subscriptions'");
if ($subCheck && $subCheck->num_rows > 0) {
    $subRow = $conn->query("SELECT COALESCE(SUM(amount),0) AS rev FROM subscriptions WHERE status='active' AND YEAR(created_at)=$year AND MONTH(created_at)=$month")->fetch_assoc();
    $subRev = (float) ($subRow['rev'] ?? 0);
}
$totalRevenue = $totalFare + $subRev;

$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
$lkr = function($v) {
    return 'LKR ' . number_format($v, 2);
};

// ── 2. Create the HTML email body ─────────────────────────────────────────────
$subject = "AccessRide Operational Report — $monthName $year";
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: AccessRide System <" . ($smtpSettings['smtp_user'] ?? 'noreply@accessride.com') . ">\r\n";

$body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6fa; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .header { background-color: #0B2F89; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 1px; }
        .header h1 span { color: #FEC329; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.8; }
        .content { padding: 30px; }
        .section-title { font-size: 14px; font-weight: 800; color: #0B2F89; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #0B2F89; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; }
        .stats-grid { display: table; width: 100%; margin-bottom: 20px; }
        .stats-row { display: table-row; }
        .stat-card { display: table-cell; width: 25%; padding: 12px; text-align: center; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
        .stat-val { font-size: 20px; font-weight: 800; color: #0B2F89; margin: 5px 0; }
        .stat-lbl { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #64748b; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table th { background-color: #f1f5f9; font-weight: bold; color: #475569; }
        .table tr:last-child td { border-bottom: 0; }
        .highlight { font-weight: bold; color: #0B2F89; background-color: #eff6ff; }
        .footer { background-color: #0B2F89; color: #ffffff; padding: 15px; text-align: center; font-size: 11px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1><span>Access</span>Ride</h1>
            <p>Monthly Operational Report — $monthName $year</p>
        </div>
        <div class='content'>
            <p>Hello,</p>
            <p>Please find below the operational report for <strong>AccessRide</strong> compiled for the period of <strong>$monthName $year</strong>.</p>
            
            <div class='section-title'>Executive Summary</div>
            <div class='stats-grid'>
                <div class='stats-row'>
                    <div class='stat-card' style='background: #eff6ff; border-color: #bfdbfe;'>
                        <div class='stat-lbl' style='color: #2563eb;'>New Users</div>
                        <div class='stat-val' style='color: #2563eb;'>$newUsers</div>
                    </div>
                    <div class='stat-card' style='background: #fffbeb; border-color: #fef3c7;'>
                        <div class='stat-lbl' style='color: #d97706;'>New Drivers</div>
                        <div class='stat-val' style='color: #d97706;'>$newDrivers</div>
                    </div>
                    <div class='stat-card' style='background: #faf5ff; border-color: #e9d5ff;'>
                        <div class='stat-lbl' style='color: #7c3aed;'>Total Rides</div>
                        <div class='stat-val' style='color: #7c3aed;'>$totalRides</div>
                    </div>
                    <div class='stat-card' style='background: #ecfdf5; border-color: #a7f3d0;'>
                        <div class='stat-lbl' style='color: #059669;'>Revenue</div>
                        <div class='stat-val' style='color: #059669; font-size: 13px;'>" . $lkr($totalRevenue) . "</div>
                    </div>
                </div>
            </div>

            <div class='section-title'>Rides & Operational Metrics</div>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th style='text-align: right;'>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Rides Requested</td>
                        <td style='text-align: right; font-weight: bold;'>$totalRides</td>
                    </tr>
                    <tr>
                        <td>Completed Rides</td>
                        <td style='text-align: right; color: #059669;'>$completedRides ($completionRate%)</td>
                    </tr>
                    <tr>
                        <td>Cancelled Rides</td>
                        <td style='text-align: right; color: #dc2626;'>$cancelledRides ($cancellationRate%)</td>
                    </tr>
                    <tr>
                        <td>Pending Rides</td>
                        <td style='text-align: right;'>$pendingRides</td>
                    </tr>
                    <tr class='highlight'>
                        <td>Total Ride Fares</td>
                        <td style='text-align: right;'>" . $lkr($totalFare) . "</td>
                    </tr>
                </tbody>
            </table>

            <div class='section-title'>Revenue breakdown</div>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th style='text-align: right;'>Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Ride Fares Gross</td>
                        <td style='text-align: right;'>" . $lkr($totalFare) . "</td>
                    </tr>
                    <tr>
                        <td>Driver Subscriptions</td>
                        <td style='text-align: right;'>" . $lkr($subRev) . "</td>
                    </tr>
                    <tr class='highlight'>
                        <td>Combined Revenue</td>
                        <td style='text-align: right;'>" . $lkr($totalRevenue) . "</td>
                    </tr>
                </tbody>
            </table>

            <p style='font-size: 12px; color: #64748b; margin-top: 30px; text-align: center;'>
                This is an auto-generated notification. Please do not reply directly to this email.
            </p>
        </div>
        <div class='footer'>
            AccessRide &copy; " . date('Y') . " · Blind Assistance Ride Service
        </div>
    </div>
</body>
</html>
";

// ── 3. Send Email using SMTP if configured, else fallback to standard mail() ──
$mailSent   = false;
$methodUsed = 'PHP mail()';
$logs       = [];

if ($smtpSettings && !empty($smtpSettings['smtp_user']) && !empty($smtpSettings['smtp_pass'])) {
    $mailer = new SMTPMailer(
        $smtpSettings['smtp_host'],
        intval($smtpSettings['smtp_port']),
        $smtpSettings['smtp_user'],
        $smtpSettings['smtp_pass'],
        $smtpSettings['smtp_secure']
    );
    $mailSent   = $mailer->send($email, $subject, $body, $headers);
    $logs       = $mailer->errorLog;
    $methodUsed = 'SMTP (' . $smtpSettings['smtp_host'] . ')';
} else {
    $mailSent = @mail($email, $subject, $body, $headers);
    $logs[]   = "No SMTP configurations found. Fallback to PHP mail() returned " . ($mailSent ? "TRUE" : "FALSE");
}

echo json_encode([
    'success'   => true,
    'mail_sent' => $mailSent,
    'message'   => $mailSent 
        ? "Report successfully emailed to $email using $methodUsed." 
        : "Failed to dispatch email. (Make sure your System Settings SMTP configurations are valid.)",
    'debug'     => $logs
]);
?>
