<?php
include_once __DIR__ . '/../config/Encryption.php';

class Settings
{
  private $conn;
  private $table = 'settings';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  private function ensureSettingsExist($admin_id)
  {
    // Dynamically alter settings table to add SMTP fields if missing
    $check_smtp = $this->conn->query("SHOW COLUMNS FROM " . $this->table . " LIKE 'smtp_host'");
    if ($check_smtp && $check_smtp->num_rows === 0) {
        $this->conn->query("ALTER TABLE " . $this->table . " 
            ADD COLUMN smtp_host VARCHAR(255) DEFAULT 'smtp.gmail.com',
            ADD COLUMN smtp_port INT DEFAULT 465,
            ADD COLUMN smtp_user VARCHAR(255) DEFAULT '',
            ADD COLUMN smtp_pass VARCHAR(255) DEFAULT '',
            ADD COLUMN smtp_secure VARCHAR(50) DEFAULT 'ssl'
        ");
    }

    $sql = 'SELECT id FROM ' . $this->table . ' WHERE admin_id = ?';
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      // Insert default row
      $insert_sql = 'INSERT INTO ' . $this->table . " (admin_id, sos_alert, ride_alert, driver_alert, email_notifications, theme, refresh_rate, sos_enabled, tracking_enabled) VALUES (?, 1, 1, 1, 0, 'light', 5, 1, 1)";
      $insert_stmt = $this->conn->prepare($insert_sql);
      $insert_stmt->bind_param('i', $admin_id);
      $insert_stmt->execute();
    }
  }

  // GET SETTINGS FOR AN ADMIN
  public function getSettingsByAdminId($admin_id)
  {
    $this->ensureSettingsExist($admin_id);
    $sql = 'SELECT * FROM ' . $this->table . ' WHERE admin_id = ?';
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      // Format properties
      $row['id'] = (int) $row['id'];
      $row['admin_id'] = (int) $row['admin_id'];
      $row['sos_alert'] = (int) $row['sos_alert'];
      $row['ride_alert'] = (int) $row['ride_alert'];
      $row['driver_alert'] = (int) $row['driver_alert'];
      $row['email_notifications'] = (int) $row['email_notifications'];
      $row['refresh_rate'] = (int) $row['refresh_rate'];
      $row['sos_enabled'] = (int) $row['sos_enabled'];
      $row['tracking_enabled'] = (int) $row['tracking_enabled'];

      return $row;
    }

    return null;
  }

  // GET NOTIFICATION SETTINGS
  public function getNotifications($admin_id)
  {
    $this->ensureSettingsExist($admin_id);
    $stmt = $this->conn->prepare('
            SELECT
                sos_alert,
                ride_alert,
                driver_alert,
                email_notifications
            FROM ' . $this->table . '
            WHERE admin_id=?
        ');
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
      $res['sos_alert'] = (int) $res['sos_alert'];
      $res['ride_alert'] = (int) $res['ride_alert'];
      $res['driver_alert'] = (int) $res['driver_alert'];
      $res['email_notifications'] = (int) $res['email_notifications'];
    }
    return $res;
  }

  // UPDATE NOTIFICATION SETTINGS
  public function updateNotifications($admin_id, $data)
  {
    $this->ensureSettingsExist($admin_id);
    $stmt = $this->conn->prepare('
            UPDATE ' . $this->table . '
            SET
                sos_alert=?,
                ride_alert=?,
                driver_alert=?,
                email_notifications=?
            WHERE admin_id=?
        ');
    $stmt->bind_param(
      'iiiii',
      $data['sos_alert'],
      $data['ride_alert'],
      $data['driver_alert'],
      $data['email_notifications'],
      $admin_id
    );
    return $stmt->execute();
  }

  // GET SYSTEM SETTINGS
  public function getSystemSettings($admin_id)
  {
    $this->ensureSettingsExist($admin_id);
    $stmt = $this->conn->prepare('
            SELECT
                theme,
                refresh_rate,
                sos_enabled,
                tracking_enabled,
                smtp_host,
                smtp_port,
                smtp_user,
                smtp_pass,
                smtp_secure
            FROM ' . $this->table . '
            WHERE admin_id=?
        ');
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
      $res['refresh_rate']     = (int) $res['refresh_rate'];
      $res['sos_enabled']      = (int) $res['sos_enabled'];
      $res['tracking_enabled'] = (int) $res['tracking_enabled'];
      $res['smtp_port']        = (int) $res['smtp_port'];
      
      // Mask password so it is never exposed in plain text in the browser
      if (!empty($res['smtp_pass'])) {
          $res['smtp_pass'] = '••••••••';
      }
    }
    return $res;
  }

  // UPDATE SYSTEM SETTINGS
  public function updateSystemSettings($admin_id, $data)
  {
    $this->ensureSettingsExist($admin_id);

    // Fetch existing encrypted password to avoid overwriting with mask
    $existing_pass = "";
    $query = $this->conn->query("SELECT smtp_pass FROM " . $this->table . " WHERE admin_id = $admin_id LIMIT 1");
    if ($query) {
        $row = $query->fetch_assoc();
        $existing_pass = $row['smtp_pass'] ?? "";
    }

    $smtp_pass = $data['smtp_pass'] ?? '';
    if ($smtp_pass === '••••••••' || empty($smtp_pass)) {
        $smtp_pass_db = $existing_pass;
    } else {
        $smtp_pass_db = Encryption::encrypt($smtp_pass);
    }

    $stmt = $this->conn->prepare('
            UPDATE ' . $this->table . '
            SET
                theme=?,
                refresh_rate=?,
                sos_enabled=?,
                tracking_enabled=?,
                smtp_host=?,
                smtp_port=?,
                smtp_user=?,
                smtp_pass=?,
                smtp_secure=?
            WHERE admin_id=?
        ');
    
    $smtp_host   = trim($data['smtp_host']   ?? 'smtp.gmail.com');
    $smtp_port   = intval($data['smtp_port']   ?? 465);
    $smtp_user   = trim($data['smtp_user']   ?? '');
    $smtp_secure = trim($data['smtp_secure'] ?? 'ssl');

    $stmt->bind_param(
      'siiisssssi',
      $data['theme'],
      $data['refresh_rate'],
      $data['sos_enabled'],
      $data['tracking_enabled'],
      $smtp_host,
      $smtp_port,
      $smtp_user,
      $smtp_pass_db,
      $smtp_secure,
      $admin_id
    );
    return $stmt->execute();
  }
}
?>
