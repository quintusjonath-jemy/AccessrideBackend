<?php

class User
{
  // UML Class Diagram Attributes (Private)
  private $id;
  private $first_name;
  private $last_name;
  private $email;
  private $phone;
  private $profile_image;
  private $status;
  private $location;
  private $created_at;

  private $conn;
  private $table = 'users';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  public function getById($id)
  {
    $query = "
            SELECT id, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) AS name, email, status, location
            FROM {$this->table}
            WHERE id = ?
        ";

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param('i', $id);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
  }
}
