<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Driver.php';

class DriverController
{
  private $driver;

  public function __construct()
  {
    $db = (new Database())->connect();
    $this->driver = new Driver($db);
  }

  public function index()
  {
    try {
      echo json_encode($this->driver->getDrivers());
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function show($id)
  {
    try {
      echo json_encode($this->driver->getDriverById($id));
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function store($data)
  {
    try {
      $result = $this->driver->addDriver($data);
      echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Driver created successfully' : 'Failed to create driver'
      ]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function update($data)
  {
    try {
      $result = $this->driver->updateDriver($data);
      echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Driver updated successfully' : 'Failed to update driver'
      ]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function destroy($id)
  {
    try {
      $result = $this->driver->deleteDriver($id);
      echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Driver deleted successfully' : 'Failed to delete driver'
      ]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  // UPDATE LOCATION
  public function updateLocation($data)
  {
    try {
      $success = $this->driver->updateLocation($data);
      echo json_encode([
        'success' => (bool)$success
      ]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function toggleStatus($id)
  {
    try {
      $success = $this->driver->toggleDriverStatus($id);
      echo json_encode([
        'success' => (bool)$success
      ]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }
}
?>