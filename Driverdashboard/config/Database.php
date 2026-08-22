<?php

class Database
{
  private $host;
  private $user;
  private $password;
  private $database;
  private $port;

  public $conn;

  public function __construct()
  {
    $envPath = __DIR__ . '/../../.env';
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

    $this->host = getenv('DB_HOST') ?: '127.0.0.1';
    $this->user = getenv('DB_USER') ?: 'root';
    $this->password = getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: '');
    $this->database = getenv('DB_NAME') ?: 'accessride';
    $this->port = (int)(getenv('DB_PORT') ?: 3306);
  }

  public function connect()
  {
    $this->conn = new mysqli(
      $this->host,
      $this->user,
      $this->password,
      $this->database,
      $this->port
    );

    if ($this->conn->connect_error) {
      die('Connection Failed: ' . $this->conn->connect_error);
    }

    $this->conn->set_charset('utf8mb4');
    return $this->conn;
  }
}

?>
