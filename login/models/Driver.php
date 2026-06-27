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

    private static function uploadFile($file, $folder)
    {

        if (empty($file['name'])) {
            return null;
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

            $passwordHash =
                password_hash(
                    $data['password'],
                    PASSWORD_DEFAULT
                );

            /*
             USERS TABLE
            */

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (
                    first_name,
                    last_name,
                    phone,
                    password_hash,
                    is_driver
                )
                VALUES
                (
                    ?, ?, ?, ?, ?
                )"
            );

            $stmt->execute([
                $data['firstName'],
                $data['lastName'],
                $data['phone'],
                $passwordHash,
                1
            ]);

            $userId = $pdo->lastInsertId();

            /*
             FILE UPLOADS
            */

            $driverPhoto =
                self::uploadFile(
                    $files['driverPhoto'],
                    "driver_photos"
                );

            $licenseFront =
                self::uploadFile(
                    $files['licenseFront'],
                    "licenses"
                );

            $licenseBack =
                self::uploadFile(
                    $files['licenseBack'],
                    "licenses"
                );

            $registrationImage =
                self::uploadFile(
                    $files['registrationImage'],
                    "registration"
                );

            $insuranceImage =
                self::uploadFile(
                    $files['insuranceImage'],
                    "insurance"
                );

            $nicFront =
                self::uploadFile(
                    $files['nicFront'],
                    "nic"
                );

            $nicBack =
                self::uploadFile(
                    $files['nicBack'],
                    "nic"
                );

            $vehicleFront =
                self::uploadFile(
                    $files['vehicleFront'],
                    "vehicle"
                );

            $vehicleRear =
                self::uploadFile(
                    $files['vehicleRear'],
                    "vehicle"
                );

            $vehicleInterior =
                self::uploadFile(
                    $files['vehicleInterior'],
                    "vehicle"
                );

            $dashboardPhoto =
                self::uploadFile(
                    $files['dashboardPhoto'],
                    "vehicle"
                );

            /*
             DRIVERS TABLE
            */

            $stmt = $pdo->prepare(
                "INSERT INTO drivers
                (
                    user_id,
                    nic,
                    dob,
                    gender,

                    street,
                    town,
                    district,
                    province,
                    postal_code,

                    vehicle_type,
                    vehicle_brand,
                    vehicle_model,
                    vehicle_color,
                    year_manufacture,

                    vehicle_registration_number,

                    license_number,
                    license_expiry,

                    registration_expiry,
                    insurance_expiry,

                    status
                )
                VALUES
                (
                    ?,?,?,?,?,?,?,?,?,?,
                    ?,?,?,?,?,?,?,?,?,?
                )"
            );

            $stmt->execute([
                $userId,

                $data['nic'],
                $data['dob'],
                $data['gender'],

                $data['street'],
                $data['town'],
                $data['district'],
                $data['province'],
                $data['postalCode'],

                $data['vehicleType'],
                $data['vehicleBrand'],
                $data['vehicleModel'],
                $data['vehicleColor'],
                $data['yearManufacture'],

                $data['vehicleRegistrationNumber'],

                $data['licenseNumber'],
                $data['licenseExpiry'],

                $data['registrationExpiry'],
                $data['insuranceExpiry'],

                'pending'
            ]);

            $driverId = $pdo->lastInsertId();

            /*
             DRIVER DOCUMENTS TABLE
            */

            $stmt = $pdo->prepare(
                "INSERT INTO driver_documents
                (
                    driver_id,

                    driver_photo,

                    license_front,
                    license_back,

                    registration_image,

                    insurance_image,

                    nic_front,
                    nic_back,

                    vehicle_front,
                    vehicle_rear,
                    vehicle_interior,

                    dashboard_photo
                )
                VALUES
                (
                    ?,?,?,?,?,?,?,?,?,?,?,?
                )"
            );

            $stmt->execute([
                $driverId,

                $driverPhoto,

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

            $pdo->commit();

            return true;
        } catch (Exception $e) {

            if (isset($pdo)) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            return false;
        }
    }
    public static function updateStatus(
        $driverId,
        $status
    ) {
        try {

            $pdo =
                self::getConnection();

            $stmt =
                $pdo->prepare(
                    "UPDATE drivers
                 SET status = ?
                 WHERE id = ?"
                );

            return $stmt->execute([
                $status,
                $driverId
            ]);
        } catch (Exception $e) {

            error_log(
                $e->getMessage()
            );

            return false;
        }
    }
    public static function login($phone, $password)
    {
        try {
            $pdo = self::getConnection();

            $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE phone = ?
            AND is_driver = 1
            LIMIT 1
        ");

            $stmt->execute([$phone]);

            $user = $stmt->fetch();

            if (!$user) {
                return false;
            }

            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }

            return $user;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
