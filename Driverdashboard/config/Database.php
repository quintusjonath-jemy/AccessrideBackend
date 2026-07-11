<?php

class Database
{
  private $host = '127.0.0.1';
  private $user = 'root';
  private $password = '';
  private $database = 'accessride';

  public $conn;

  public function connect()
  {
    $this->conn = new mysqli(
      $this->host,
      $this->user,
      $this->password,
      $this->database
    );

    if ($this->conn->connect_error) {
      die('Connection Failed: ' . $this->conn->connect_error);
    }

    return $this->conn;
  }
}

?>
