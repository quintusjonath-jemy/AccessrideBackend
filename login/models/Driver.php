<?php

require_once __DIR__ . '/../config/config.php';

class Driver
{

    private static function getConnection()
    {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        return new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    private static function getTableColumns($pdo, $table)
    {
        try {
            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = [];
            while ($row = $stmt->fetch()) {
                $columns[] = $row['Field'];
            }
            return $columns;
        } catch (Exception $e) {
            return [];
        }
    }

    private static function tableExists($pdo, $table)
    {
        try {
            $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function uploadFile($file, $folder)
    {

        if (empty($file['name'])) {
            return null;
        }
        if ($file['size'] > 5 * 1024 * 1024) {

            throw new Exception("File size exceeds 5MB.");
        }

        $allowed = [

            "image/jpeg",
            "image/png",
            "image/jpg"

        ];

        $mime = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowed)) {

            throw new Exception("Invalid image.");
        }



        $targetDir = __DIR__ . "/../uploads/" . $folder . "/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName =
            time() . "_" .
            preg_replace("/[^a-zA-Z0-9._-]/", "", $file['name']);

        move_uploaded_file(
            $file['tmp_name'],
            $targetDir . $fileName
        );

        return $fileName;
    }

    public static function register($data, $files)
    {
        try {
            $pdo = self::getConnection();
            $pdo->beginTransaction();

            // Check if phone already exists in drivers table
            $stmt = $pdo->prepare("
                SELECT id
                FROM drivers
                WHERE phone = ?
                LIMIT 1
            ");
            $stmt->execute([$data['phone']]);
            if ($stmt->fetch()) {
                throw new Exception("Phone number already registered.");
            }

            $passwordHash = password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            );

            $email = isset($data['email']) && !empty($data['email']) ? $data['email'] : ($data['phone'] . '@accessride.com');

            // Handle file uploads
            $driverPhoto = self::uploadFile($files['driverPhoto'] ?? null, "driver_photos");
            $licenseFront = self::uploadFile($files['licenseFront'] ?? null, "licenses");
            $licenseBack = self::uploadFile($files['licenseBack'] ?? null, "licenses");
            $registrationImage = self::uploadFile($files['registrationImage'] ?? null, "registration");
            $insuranceImage = self::uploadFile($files['insuranceImage'] ?? null, "insurance");
            $nicFront = self::uploadFile($files['nicFront'] ?? null, "nic");
            $nicBack = self::uploadFile($files['nicBack'] ?? null, "nic");
            $vehicleFront = self::uploadFile($files['vehicleFront'] ?? null, "vehicle");
            $vehicleRear = self::uploadFile($files['vehicleRear'] ?? null, "vehicle");
            $vehicleInterior = self::uploadFile($files['vehicleInterior'] ?? null, "vehicle");
            $dashboardPhoto = self::uploadFile($files['dashboardPhoto'] ?? null, "vehicle");

            // 1. DYNAMICALLY BUILD DRIVER INSERT
            $driverFields = [
                'first_name' => $data['firstName'],
                'last_name' => $data['lastName'],
                'email' => $email,
                'phone' => $data['phone'],
                'password' => $passwordHash,
                'profile_image' => $driverPhoto,
                'status' => 'offline',
                'nic' => $data['nic'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'] ?? null,
                'street' => $data['street'] ?? null,
                'town' => $data['town'] ?? null,
                'district' => $data['district'] ?? null,
                'province' => $data['province'] ?? null,
                'postal_code' => $data['postalCode'] ?? null,
                'license_number' => $data['licenseNumber'] ?? null,
                'license_expiry' => $data['licenseExpiry'] ?? null,
                'registration_expiry' => $data['registrationExpiry'] ?? null,
                'insurance_expiry' => $data['insuranceExpiry'] ?? null
            ];

            // Get columns of drivers table
            $driverColumns = self::getTableColumns($pdo, 'drivers');
            if (empty($driverColumns)) {
                // Fallback if describe fails
                $driverColumns = ['first_name', 'last_name', 'email', 'phone', 'password', 'profile_image', 'status'];
            }

            $driverInsertData = [];
            foreach ($driverFields as $col => $val) {
                if (in_array($col, $driverColumns)) {
                    $driverInsertData[$col] = $val;
                }
            }

            $colsStr = implode(', ', array_map(function($c) { return "`$c`"; }, array_keys($driverInsertData)));
            $valsPlaceholders = implode(', ', array_fill(0, count($driverInsertData), '?'));
            
            $stmt = $pdo->prepare("INSERT INTO drivers ($colsStr) VALUES ($valsPlaceholders)");
            $stmt->execute(array_values($driverInsertData));

            $driverId = $pdo->lastInsertId();

            // 2. DYNAMICALLY BUILD VEHICLE INSERT
            $vehicleFields = [
                'driver_id' => $driverId,
                'vehicle_number' => $data['vehicleRegistrationNumber'] ?? '',
                'vehicle_type' => $data['vehicleType'] ?? '',
                'vehicle_brand' => $data['vehicleBrand'] ?? null,
                'vehicle_model' => $data['vehicleModel'] ?? null,
                'vehicle_color' => $data['vehicleColor'] ?? null,
                'year_manufacture' => isset($data['yearManufacture']) && trim($data['yearManufacture']) !== '' ? intval($data['yearManufacture']) : null
            ];

            $vehicleColumns = self::getTableColumns($pdo, 'vehicles');
            if (empty($vehicleColumns)) {
                $vehicleColumns = ['driver_id', 'vehicle_number', 'vehicle_type'];
            }

            $vehicleInsertData = [];
            foreach ($vehicleFields as $col => $val) {
                if (in_array($col, $vehicleColumns)) {
                    $vehicleInsertData[$col] = $val;
                }
            }

            $vColsStr = implode(', ', array_map(function($c) { return "`$c`"; }, array_keys($vehicleInsertData)));
            $vValsPlaceholders = implode(', ', array_fill(0, count($vehicleInsertData), '?'));

            $stmt = $pdo->prepare("INSERT INTO vehicles ($vColsStr) VALUES ($vValsPlaceholders)");
            $stmt->execute(array_values($vehicleInsertData));

            // 3. DYNAMICALLY INSERT DOCUMENTS IF TABLE EXISTS
            if (self::tableExists($pdo, 'driver_documents')) {
                $stmt = $pdo->prepare("
                    INSERT INTO driver_documents (
                        driver_id, license_front, license_back, registration_image, insurance_image,
                        nic_front, nic_back, vehicle_front, vehicle_rear, vehicle_interior, dashboard_photo
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $driverId,
                    $licenseFront,
                    $licenseBack,
                    $registrationImage,
                    $insuranceImage,
                    $nicFront,
                    $nicBack,
                    $vehicleFront,
                    $vehicleRear,
                    $vehicleInterior,
                    $dashboardPhoto
                ]);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());
            throw $e;
        }
    }

    public static function updateStatus($driverId, $status)
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare("
                UPDATE drivers
                SET status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $status,
                $driverId
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function login($phone, $password)
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare("
                SELECT id, first_name, last_name, email, phone, password, status
                FROM drivers
                WHERE phone = ?
                LIMIT 1
            ");
            $stmt->execute([$phone]);
            $driver = $stmt->fetch();

            if (!$driver) {
                return false;
            }

            if (!password_verify($password, $driver['password'])) {
                return false;
            }
            unset($driver['password']);
            return $driver;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
