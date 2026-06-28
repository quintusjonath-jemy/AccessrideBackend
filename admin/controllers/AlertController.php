<?php

include_once '../config/Database.php';
include_once '../models/Alert.php';

class AlertController
{
  private $alert;

  public function __construct()
  {
    $database = new Database();

    $db = $database->connect();

    $this->alert = new Alert($db);
  }

  // GET ALERTS
  public function index()
  {
    echo json_encode(
      $this->alert->getAlerts()
    );
  }

  // ADD ALERT
  public function store($data)
  {
    $success = $this->alert->addAlert($data);

    echo json_encode([
      'success' => $success
    ]);
  }

  // RESOLVE ALERT
  public function resolve($id)
  {
    $success = $this->alert->resolveAlert($id);

    echo json_encode([
      'success' => $success
    ]);
  }
}

?>