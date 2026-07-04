<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private static function getConnection() {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public static function save(array $data) {
        if (!is_array($data)) {
            return false;
        }

        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, phone, password_hash, guardian_name, guardian_number, is_driver) VALUES (:first_name, :last_name, :email, :phone, :password_hash, :guardian_name, :guardian_number, :is_driver)'
            );

            $stmt->execute([
                ':first_name' => isset($data['firstName']) ? $data['firstName'] : '',
                ':last_name' => isset($data['lastName']) ? $data['lastName'] : '',
                ':email' => isset($data['email']) ? $data['email'] : '',
                ':phone' => isset($data['phone']) ? $data['phone'] : '',
                ':password_hash' => password_hash(isset($data['password']) ? $data['password'] : '', PASSWORD_DEFAULT),
                ':guardian_name' => isset($data['guardianName']) ? $data['guardianName'] : null,
                ':guardian_number' => isset($data['guardianNumber']) ? $data['guardianNumber'] : null,
                ':is_driver' => !empty($data['isDriver']) ? 1 : 0,
                
            ]);

            return true;
        } catch (Exception $e) {
            echo "<pre>";
            print_r($e->getMessage());
            echo "</pre>";
            exit;
        }
    }


    public static function current(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function isAuthenticated(): bool {
        return isset($_SESSION['user']);
    }

    public static function findByEmail(string $email): ?array {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log('DB find by email failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function findByPhone(string $phone): ?array {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = :phone AND is_driver = 1 LIMIT 1');
            $stmt->execute([':phone' => $phone]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log('DB find by phone failed: ' . $e->getMessage());
            return null;
        }
    }
}
