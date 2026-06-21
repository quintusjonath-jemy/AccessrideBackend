<?php

include_once '../config/Database.php';
include_once '../models/User.php';

class UserController
{
  private $user;

  public function __construct()
  {
    $database = new Database();

    $db = $database->connect();

    $this->user = new User($db);
  }

  // GET USERS
  public function index()
  {
    echo json_encode(
      $this->user->getUsers()
    );
  }

  // ADD USER
  public function store($data)
  {
    $success = $this->user->addUser($data);

    echo json_encode([
      'success' => $success
    ]);
  }

  // UPDATE USER
  public function update($data)
  {
    $success = $this->user->updateUser($data);

    echo json_encode([
      'success' => $success
    ]);
  }

  // DELETE USER
  public function destroy($id)
  {
    $success = $this->user->deleteUser($id);

    echo json_encode([
      'success' => $success
    ]);
  }

  // HIDE USER
  public function toggleStatus($id)
  {
    echo json_encode([
      'success' =>
        $this->user->toggleUserStatus($id)
    ]);
  }
}

?>