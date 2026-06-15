<?php

class Driver {

    private $conn;
    private $table = "drivers";

    public function __construct($db) {
        $this->conn = $db;
    }

    // GET ALL DRIVERS
    public function getDrivers() {
        // Auto-expire checks: check for drivers whose subscription has active status but expired date
        $today = date('Y-m-d');
        $expiredQuery = "SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name FROM drivers WHERE subscription_status = 'active' AND subscription_expires_at < '$today'";
        $expiredRes = $this->conn->query($expiredQuery);
        if ($expiredRes && $expiredRes->num_rows > 0) {
            while ($row = $expiredRes->fetch_assoc()) {
                $driverId = (int)$row['id'];
                $driverName = $row['name'];
                
                // Update driver subscription status to expired
                $this->conn->query("UPDATE drivers SET subscription_status = 'expired' WHERE id = $driverId");
                
                // Log notification
                $msg = "Driver " . $driverName . "'s monthly membership subscription has expired.";
                $stmtNotif = $this->conn->prepare("INSERT INTO admin_notifications (type, message) VALUES ('Driver', ?)");
                $stmtNotif->bind_param("s", $msg);
                $stmtNotif->execute();
            }
        }

        $result = $this->conn->query("SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, phone, vehicle_number, vehicle_type, status, current_location, created_at, latitude, longitude, subscription_status, subscription_expires_at, last_payment_date, subscription_amount FROM drivers");
        $drivers = [];

        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }

        return $drivers;
    }

    // GET ONE DRIVER
    public function getDriverById($id) {
        $stmt = $this->conn->prepare("SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, phone, vehicle_number, vehicle_type, status, current_location, created_at, latitude, longitude, subscription_status, subscription_expires_at, last_payment_date, subscription_amount FROM drivers WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // ADD DRIVER
    public function addDriver($data) {
        $sql = "
            INSERT INTO drivers
            (
                first_name,
                last_name,
                email,
                phone,
                vehicle_number,
                vehicle_type,
                status,
                current_location,
                subscription_status,
                subscription_expires_at,
                last_payment_date,
                subscription_amount
            )

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $parts = explode(' ', trim($data['name']), 2);
        $first_name = $parts[0];
        $last_name = isset($parts[1]) ? $parts[1] : '';

        $stmt = $this->conn->prepare($sql);

        $sub_status = isset($data['subscription_status']) ? $data['subscription_status'] : 'none';
        $sub_expires = !empty($data['subscription_expires_at']) ? $data['subscription_expires_at'] : null;
        $last_pay = !empty($data['last_payment_date']) ? $data['last_payment_date'] : null;
        $sub_amount = isset($data['subscription_amount']) ? (float)$data['subscription_amount'] : 29.99;

        $stmt->bind_param(
            "sssssssssssd",
            $first_name,
            $last_name,
            $data['email'],
            $data['phone'],
            $data['vehicle_number'],
            $data['vehicle_type'],
            $data['status'],
            $data['current_location'],
            $sub_status,
            $sub_expires,
            $last_pay,
            $sub_amount
        );

        return $stmt->execute();
    }

    // UPDATE DRIVER
    public function updateDriver($data) {
        // Log changes in subscription status if any
        $oldRes = $this->conn->query("SELECT subscription_status, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name FROM drivers WHERE id = " . (int)$data['id']);
        if ($oldRes) {
            $oldRow = $oldRes->fetch_assoc();
            if ($oldRow) {
                $oldStatus = $oldRow['subscription_status'];
                $newStatus = isset($data['subscription_status']) ? $data['subscription_status'] : 'none';
                if ($oldStatus !== $newStatus) {
                    $driverName = $oldRow['name'];
                    $msg = "";
                    if ($newStatus === 'active') {
                        $msg = "Driver " . $driverName . "'s membership subscription has been renewed (status: Active).";
                    } elseif ($newStatus === 'expired') {
                        $msg = "Driver " . $driverName . "'s membership subscription has been marked as Expired.";
                    } else {
                        $msg = "Driver " . $driverName . "'s membership subscription has been updated to None.";
                    }
                    $stmtNotif = $this->conn->prepare("INSERT INTO admin_notifications (type, message) VALUES ('Driver', ?)");
                    $stmtNotif->bind_param("s", $msg);
                    $stmtNotif->execute();
                }
            }
        }

        $sql = "
            UPDATE drivers
            SET
                first_name=?,
                last_name=?,
                email=?,
                phone=?,
                vehicle_number=?,
                vehicle_type=?,
                status=?,
                current_location=?,
                subscription_status=?,
                subscription_expires_at=?,
                last_payment_date=?,
                subscription_amount=?
            WHERE id=?
        ";

        $parts = explode(' ', trim($data['name']), 2);
        $first_name = $parts[0];
        $last_name = isset($parts[1]) ? $parts[1] : '';

        $stmt = $this->conn->prepare($sql);

        $sub_status = isset($data['subscription_status']) ? $data['subscription_status'] : 'none';
        $sub_expires = !empty($data['subscription_expires_at']) ? $data['subscription_expires_at'] : null;
        $last_pay = !empty($data['last_payment_date']) ? $data['last_payment_date'] : null;
        $sub_amount = isset($data['subscription_amount']) ? (float)$data['subscription_amount'] : 29.99;

        $stmt->bind_param(
            "ssssssssssds", // bind id as string/integer (bind_param coerces anyway, but let's match count: 13 variables)
            $first_name,
            $last_name,
            $data['email'],
            $data['phone'],
            $data['vehicle_number'],
            $data['vehicle_type'],
            $data['status'],
            $data['current_location'],
            $sub_status,
            $sub_expires,
            $last_pay,
            $sub_amount,
            $data['id']
        );

        return $stmt->execute();
    }

    // DELETE DRIVER
    public function deleteDriver($id) {
        $stmt = $this->conn->prepare("DELETE FROM drivers WHERE id=?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // UPDATE DRIVER LOCATION
    public function updateLocation($data) {

        $sql = "
            UPDATE drivers
            SET latitude=?, longitude=?
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ddi",
            $data['latitude'],
            $data['longitude'],
            $data['id']
        );

        return $stmt->execute();
    }

    public function toggleDriverStatus($id) {

        $stmt = $this->conn->prepare("SELECT status FROM drivers WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $driver = $stmt->get_result()->fetch_assoc();

        if (!$driver) return false;

        $current = strtolower($driver['status']);

        // only toggle block/unblock safely
        if ($current === 'blocked') {
            // restore to offline (safe default)
            $newStatus = 'offline';
        } else {
            $newStatus = 'blocked';
        }

        $stmt = $this->conn->prepare("UPDATE drivers SET status=? WHERE id=?");
        $stmt->bind_param("si", $newStatus, $id);

        return $stmt->execute();
    }
}
?>