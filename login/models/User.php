<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private static function getConnection() {
        $port = defined('DB_PORT') ? DB_PORT : (getenv('DB_PORT') ?: '3306');
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, $port, DB_NAME, DB_CHARSET);
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
            $user = new self();
            $user->first_name = isset($data['firstName']) ? $data['firstName'] : '';
            $user->last_name = isset($data['lastName']) ? $data['lastName'] : '';
            $user->email = isset($data['email']) ? $data['email'] : '';
            $user->phone = isset($data['phone']) ? $data['phone'] : '';
            $user->location = isset($data['homeAddress']) ? $data['homeAddress'] : (isset($data['location']) ? $data['location'] : (isset($data['address']) ? $data['address'] : ''));

            $pdo = self::getConnection();
            $pdo->beginTransaction();

            // Detect existing columns in users table
            $colsQuery = $pdo->query("SHOW COLUMNS FROM users");
            $existingCols = $colsQuery ? $colsQuery->fetchAll(PDO::FETCH_COLUMN) : [];

            $userFields = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password_hash' => password_hash(isset($data['password']) ? $data['password'] : '', PASSWORD_DEFAULT)
            ];

            if (empty($existingCols) || in_array('location', $existingCols)) {
                $userFields['location'] = $user->location;
            }
            if (in_array('address', $existingCols)) {
                $userFields['address'] = $user->location;
            }

            $colNames = implode(', ', array_map(function($c) { return "`$c`"; }, array_keys($userFields)));
            $placeholders = implode(', ', array_map(function($c) { return ":$c"; }, array_keys($userFields)));

            $stmt = $pdo->prepare("INSERT INTO users ($colNames) VALUES ($placeholders)");
            $params = [];
            foreach ($userFields as $k => $v) {
                $params[":$k"] = $v;
            }
            $stmt->execute($params);

            $userId = $pdo->lastInsertId();
            $user->id = $userId;

            if (!empty($data['guardianName']) && !empty($data['guardianNumber'])) {
                $stmtGuardian = $pdo->prepare(
                    'INSERT INTO emergency_contacts (user_id, contact_name, relationship, phone_number) VALUES (:user_id, :contact_name, :relationship, :phone_number)'
                );
                $stmtGuardian->execute([
                    ':user_id' => $userId,
                    ':contact_name' => $data['guardianName'],
                    ':relationship' => 'guardian',
                    ':phone_number' => $data['guardianNumber']
                ]);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Return JSON response rather than HTML <pre> so frontend gets clean error messages
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
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
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByPhone(string $phone): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = :phone AND is_driver = 1 LIMIT 1');
        $stmt->execute([':phone' => $phone]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}
