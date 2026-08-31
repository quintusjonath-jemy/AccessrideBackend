<?php

class Driver
{
  // UML Class Diagram Attributes (Private)
  private $id;
  private $first_name;
  private $last_name;
  private $email;
  private $phone;
  private $profile_image;
  private $status;
  private $current_location;
  private $license_plate_front;
  private $license_plate_back;
  private $created_at;

  private $conn;
  private $table = 'drivers';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // GET ALL DRIVERS
  public function getDrivers()
  {
    // Auto-expire checks: check for drivers whose subscription has active status but expired date
    $today = date('Y-m-d');
    $expiredQuery = "
            SELECT d.id, d.phone, TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name, s.expires_at 
            FROM drivers d
            JOIN subscriptions s ON d.id = s.driver_id
            WHERE s.status = 'active' AND s.expires_at < '$today'
        ";
    $expiredRes = $this->conn->query($expiredQuery);
    if ($expiredRes && $expiredRes->num_rows > 0) {
      while ($row = $expiredRes->fetch_assoc()) {
        $driverId = (int) $row['id'];
        $driverName = $row['name'];
        $driverPhone = $row['phone'];
        $expiryDate = $row['expires_at'];

        // Update driver subscription status to expired
        $stmtExp = $this->conn->prepare("UPDATE subscriptions SET status = 'expired' WHERE driver_id = ?");
        if ($stmtExp) {
          $stmtExp->bind_param('i', $driverId);
          $stmtExp->execute();
          $stmtExp->close();
        }

        // Log notification
        $msg = 'Driver ' . $driverName . "'s monthly membership subscription has expired.";
        $stmtNotif = $this->conn->prepare("INSERT INTO admin_notifications (type, message) VALUES ('Driver', ?)");
        $stmtNotif->bind_param('s', $msg);
        $stmtNotif->execute();

        // Send warning SMS to the specific driver phone number when subscription expires
        if (!empty($driverPhone)) {
          $smsMsg = "AccessRide Notice: Dear {$driverName}, your subscription has expired on {$expiryDate}. Please activate it on your dashboard to continue receiving bookings.";
          
          // Get Twilio config from settings
          $twilio_sid = '';
          $twilio_token = '';
          $twilio_from = '';
          $settingsRes = $this->conn->query("SELECT * FROM settings WHERE admin_id = 1 LIMIT 1");
          if ($settingsRes && $settingsRes->num_rows > 0) {
            $settingsRow = $settingsRes->fetch_assoc();
            $twilio_sid = $settingsRow['twilio_sid'] ?? '';
            $twilio_token_enc = $settingsRow['twilio_token'] ?? '';
            $twilio_from = $settingsRow['twilio_from'] ?? '';
            if (!empty($twilio_token_enc)) {
              include_once __DIR__ . '/../config/Encryption.php';
              try {
                $twilio_token = Encryption::decrypt($twilio_token_enc);
              } catch (Exception $e) {
                $twilio_token = $twilio_token_enc;
              }
            }
          }

          $smsSent = false;
          include_once __DIR__ . '/../config/sms.php';
          $twilio = new TwilioSMS($twilio_sid, $twilio_token, $twilio_from);
          $smsSent = $twilio->send($driverPhone, $smsMsg);

          $logDir = __DIR__ . '/../../logs';
          if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
          }
          $statusStr = $smsSent ? "delivered" : "failed/mocked";
          file_put_contents($logDir . '/sms_log.txt', "[" . date('Y-m-d H:i:s') . "] SMS Expiry ({$statusStr}) sent to {$driverPhone}: {$smsMsg}\n", FILE_APPEND);
        }
      }
    }

    $result = $this->conn->query("
            SELECT 
                d.id, 
                TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name, 
                d.first_name,
                d.last_name,
                d.email, 
                d.phone, 
                d.profile_image,
                d.nic,
                d.license_number,
                d.license_expiry,
                d.registration_expiry,
                d.insurance_expiry,
                v.vehicle_number, 
                v.vehicle_type, 
                d.status, 
                d.current_location, 
                d.created_at, 
                d.latitude, 
                d.longitude, 
                s.status AS subscription_status, 
                s.expires_at AS subscription_expires_at, 
                s.last_payment_date, 
                s.amount AS subscription_amount,
                doc.license_front,
                doc.license_back,
                doc.nic_front,
                doc.nic_back,
                doc.registration_image,
                doc.insurance_image,
                doc.vehicle_front,
                doc.vehicle_rear,
                doc.vehicle_interior,
                doc.dashboard_photo,
                (CASE WHEN doc.id IS NOT NULL OR doc.license_front IS NOT NULL THEN 1 ELSE 0 END) AS has_documents,
                COALESCE((
                    SELECT SUM(r_rating.rating) / NULLIF(COUNT(r_rating.id), 0)
                    FROM rides r_rating
                    WHERE r_rating.driver_id = d.id
                      AND r_rating.status = 'completed'
                      AND DATE_FORMAT(r_rating.ride_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
                ), 0.0) AS monthly_rating
            FROM drivers d 
            LEFT JOIN vehicles v ON d.id = v.driver_id
            LEFT JOIN subscriptions s ON d.id = s.driver_id
            LEFT JOIN driver_documents doc ON d.id = doc.driver_id
        ");
    $drivers = [];

    while ($row = $result->fetch_assoc()) {
      $row['monthly_rating'] = (float)$row['monthly_rating'];
      $row['has_documents'] = (bool)$row['has_documents'];
      $drivers[] = $row;
    }

    return $drivers;
  }

  // GET ONE DRIVER
  public function getDriverById($id)
  {
    $stmt = $this->conn->prepare("
            SELECT 
                d.id, 
                TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name, 
                d.first_name,
                d.last_name,
                d.email, 
                d.phone, 
                d.profile_image,
                d.nic,
                d.dob,
                d.gender,
                d.street,
                d.town,
                d.district,
                d.province,
                d.postal_code,
                d.license_number,
                d.license_expiry,
                d.registration_expiry,
                d.insurance_expiry,
                v.vehicle_number, 
                v.vehicle_type, 
                d.status, 
                d.current_location, 
                d.created_at, 
                d.latitude, 
                d.longitude, 
                s.status AS subscription_status, 
                s.expires_at AS subscription_expires_at, 
                s.last_payment_date, 
                s.amount AS subscription_amount,
                doc.license_front,
                doc.license_back,
                doc.registration_image,
                doc.insurance_image,
                doc.nic_front,
                doc.nic_back,
                doc.vehicle_front,
                doc.vehicle_rear,
                doc.vehicle_interior,
                doc.dashboard_photo,
                (SELECT COUNT(*) FROM rides r WHERE r.driver_id = d.id AND r.status = 'completed') AS completed_rides_count,
                (SELECT COALESCE(SUM(r.fare), 0) FROM rides r WHERE r.driver_id = d.id AND r.status = 'completed') AS gross_earnings,
                COALESCE((
                    SELECT SUM(r_rating.rating) / NULLIF(COUNT(r_rating.id), 0)
                    FROM rides r_rating
                    WHERE r_rating.driver_id = d.id
                      AND r_rating.status = 'completed'
                      AND DATE_FORMAT(r_rating.ride_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
                ), 0.0) AS monthly_rating
            FROM drivers d 
            LEFT JOIN vehicles v ON d.id = v.driver_id 
            LEFT JOIN subscriptions s ON d.id = s.driver_id 
            LEFT JOIN driver_documents doc ON d.id = doc.driver_id
            WHERE d.id=?
        ");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $result['completed_rides_count'] = (int) $result['completed_rides_count'];
        $result['gross_earnings'] = (float) $result['gross_earnings'];
        $result['monthly_rating'] = (float) $result['monthly_rating'];
    }
    return $result;
  }

  // ADD DRIVER
  public function addDriver($data)
  {
    $sql = '
            INSERT INTO drivers
            (
                first_name,
                last_name,
                email,
                phone,
                status,
                current_location
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ';

    $parts = explode(' ', trim($data['name']), 2);
    $first_name = $parts[0];
    $last_name = isset($parts[1]) ? $parts[1] : '';

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
      'ssssss',
      $first_name,
      $last_name,
      $data['email'],
      $data['phone'],
      $data['status'],
      $data['current_location']
    );

    if ($stmt->execute()) {
      $driver_id = $this->conn->insert_id;

      // Insert vehicle
      $veh_stmt = $this->conn->prepare('INSERT INTO vehicles (driver_id, vehicle_number, vehicle_type) VALUES (?, ?, ?)');
      $veh_stmt->bind_param(
        'iss',
        $driver_id,
        $data['vehicle_number'],
        $data['vehicle_type']
      );
      $veh_stmt->execute();

      // Insert subscription
      $sub_status = isset($data['subscription_status']) ? $data['subscription_status'] : 'none';
      $sub_expires = !empty($data['subscription_expires_at']) ? $data['subscription_expires_at'] : null;
      $last_pay = !empty($data['last_payment_date']) ? $data['last_payment_date'] : null;
      $sub_amount = isset($data['subscription_amount']) ? (float) $data['subscription_amount'] : 29.99;

      $sub_stmt = $this->conn->prepare('INSERT INTO subscriptions (driver_id, status, expires_at, last_payment_date, amount, warning_sent) VALUES (?, ?, ?, ?, ?, 0)');
      $sub_stmt->bind_param(
        'isssd',
        $driver_id,
        $sub_status,
        $sub_expires,
        $last_pay,
        $sub_amount
      );
      return $sub_stmt->execute();
    }
    return false;
  }

  // UPDATE DRIVER
  public function updateDriver($data)
  {
    // Log changes in subscription status if any
    $oldRes = $this->conn->query("
            SELECT 
                s.status AS subscription_status, 
                TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) AS name 
            FROM drivers d 
            LEFT JOIN subscriptions s ON d.id = s.driver_id 
            WHERE d.id = " . (int) $data['id']);
    if ($oldRes) {
      $oldRow = $oldRes->fetch_assoc();
      if ($oldRow) {
        $oldStatus = $oldRow['subscription_status'];
        $newStatus = isset($data['subscription_status']) ? $data['subscription_status'] : 'none';
        if ($oldStatus !== $newStatus) {
          $driverName = $oldRow['name'];
          $msg = '';
          if ($newStatus === 'active') {
            $msg = 'Driver ' . $driverName . "'s membership subscription has been renewed (status: Active).";
          } elseif ($newStatus === 'expired') {
            $msg = 'Driver ' . $driverName . "'s membership subscription has been marked as Expired.";
          } else {
            $msg = 'Driver ' . $driverName . "'s membership subscription has been updated to None.";
          }
          $stmtNotif = $this->conn->prepare("INSERT INTO admin_notifications (type, message) VALUES ('Driver', ?)");
          $stmtNotif->bind_param('s', $msg);
          $stmtNotif->execute();
        }
      }
    }

    $sql = '
            UPDATE drivers
            SET
                first_name=?,
                last_name=?,
                email=?,
                phone=?,
                status=?,
                current_location=?
            WHERE id=?
        ';

    $parts = explode(' ', trim($data['name']), 2);
    $first_name = $parts[0];
    $last_name = isset($parts[1]) ? $parts[1] : '';

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
      'ssssssi',
      $first_name,
      $last_name,
      $data['email'],
      $data['phone'],
      $data['status'],
      $data['current_location'],
      $data['id']
    );

    if ($stmt->execute()) {
      $driver_id = (int) $data['id'];

      // Upsert vehicle
      $veh_stmt = $this->conn->prepare('
                INSERT INTO vehicles (driver_id, vehicle_number, vehicle_type)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE vehicle_number=?, vehicle_type=?
            ');
      $veh_stmt->bind_param(
        'issss',
        $driver_id,
        $data['vehicle_number'],
        $data['vehicle_type'],
        $data['vehicle_number'],
        $data['vehicle_type']
      );
      $veh_stmt->execute();

      // Upsert subscription
      $sub_status = isset($data['subscription_status']) ? $data['subscription_status'] : 'none';
      $sub_expires = !empty($data['subscription_expires_at']) ? $data['subscription_expires_at'] : null;
      $last_pay = !empty($data['last_payment_date']) ? $data['last_payment_date'] : null;
      $sub_amount = isset($data['subscription_amount']) ? (float) $data['subscription_amount'] : 29.99;

      $sub_stmt = $this->conn->prepare('
                INSERT INTO subscriptions (driver_id, status, expires_at, last_payment_date, amount, warning_sent)
                VALUES (?, ?, ?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE status=?, expires_at=?, last_payment_date=?, amount=?, warning_sent=0
            ');
      $sub_stmt->bind_param(
        'isssdsssd',
        $driver_id,
        $sub_status,
        $sub_expires,
        $last_pay,
        $sub_amount,
        $sub_status,
        $sub_expires,
        $last_pay,
        $sub_amount
      );
      return $sub_stmt->execute();
    }
    return false;
  }

  // DELETE DRIVER
  public function deleteDriver($id)
  {
    $stmt = $this->conn->prepare('DELETE FROM drivers WHERE id=?');
    $stmt->bind_param('i', $id);

    return $stmt->execute();
  }

  // UPDATE DRIVER LOCATION
  public function updateLocation($data)
  {
    $sql = '
            UPDATE drivers
            SET latitude=?, longitude=?
            WHERE id=?
        ';

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
      'ddi',
      $data['latitude'],
      $data['longitude'],
      $data['id']
    );

    return $stmt->execute();
  }

  public function toggleDriverStatus($id)
  {
    $stmt = $this->conn->prepare('SELECT status FROM drivers WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $driver = $stmt->get_result()->fetch_assoc();

    if (!$driver)
      return false;

    $current = strtolower($driver['status']);

    // only toggle block/unblock safely
    if ($current === 'blocked') {
      // restore to offline (safe default)
      $newStatus = 'offline';
    } else {
      $newStatus = 'blocked';
    }

    $stmt = $this->conn->prepare('UPDATE drivers SET status=? WHERE id=?');
    $stmt->bind_param('si', $newStatus, $id);

    return $stmt->execute();
  }
}
?>