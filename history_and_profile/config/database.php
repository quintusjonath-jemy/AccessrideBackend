<?php

$envPath = __DIR__ . '/../../../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$host = getenv('DB_HOST') ?: "127.0.0.1";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: "");
$database = getenv('DB_NAME') ?: "accessride";
$port = (int)(getenv('DB_PORT') ?: 3306);

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    $port
);

if($conn->connect_error){
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

?>