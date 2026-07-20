<?php

require_once __DIR__ . '/../config/config.php';

class Admin
{
    // UML Class Diagram Attributes (Private)
    private $id;
    private $name;
    private $email;
    private $phone;
    private $profile_image;
    private $password;
    private $created_at;
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

    public static function findByEmail($email)
    {
        try {

            $pdo = self::getConnection();

            $stmt = $pdo->prepare(
                "SELECT * FROM admins
                 WHERE email = ?
                 LIMIT 1"
            );

            $stmt->execute([$email]);

            return $stmt->fetch();

        } catch (Exception $e) {

            error_log($e->getMessage());

            return null;
        }
    }
}