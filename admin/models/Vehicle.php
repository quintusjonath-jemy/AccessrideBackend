<?php

class Vehicle
{
  // UML Class Diagram Attributes (Private)
  private $id;
  private $driver_id;
  private $vehicle_number;
  private $vehicle_type;
  private $vehicle_outside_image;
  private $vehicle_inside_image;
  private $insurance_card_front_image;
  private $insurance_card_back_image;
  private $vehicle_book_url;

  private $conn;
  private $table = 'vehicles';

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // GET ALL VEHICLES
  public function getVehicles()
  {
    $sql = 'SELECT * FROM ' . $this->table;
    $result = $this->conn->query($sql);
    $vehicles = [];

    while ($row = $result->fetch_assoc()) {
      $row['id'] = (int) $row['id'];
      $row['driver_id'] = (int) $row['driver_id'];
      $vehicles[] = $row;
    }

    return $vehicles;
  }

  // GET VEHICLE BY DRIVER ID
  public function getVehicleByDriverId($driver_id)
  {
    $sql = 'SELECT * FROM ' . $this->table . ' WHERE driver_id = ?';
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();

      // Format properties
      $row['id'] = (int) $row['id'];
      $row['driver_id'] = (int) $row['driver_id'];

      $this->id = $row['id'];
      $this->driver_id = $row['driver_id'];
      $this->vehicle_number = isset($row['vehicle_registration_number']) ? $row['vehicle_registration_number'] : '';
      $this->vehicle_type = isset($row['vehicle_type']) ? $row['vehicle_type'] : '';
      $this->vehicle_outside_image = isset($row['vehicle_outside_image']) ? $row['vehicle_outside_image'] : '';
      $this->vehicle_inside_image = isset($row['vehicle_inside_image']) ? $row['vehicle_inside_image'] : '';
      $this->insurance_card_front_image = isset($row['insurance_card_front_image']) ? $row['insurance_card_front_image'] : '';
      $this->insurance_card_back_image = isset($row['insurance_card_back_image']) ? $row['insurance_card_back_image'] : '';
      $this->vehicle_book_url = isset($row['vehicle_book_url']) ? $row['vehicle_book_url'] : '';

      return $row;
    }

    return null;
  }

  // ADD VEHICLE
  public function addVehicle($data)
  {
    $sql = 'INSERT INTO ' . $this->table . ' (driver_id, vehicle_number, vehicle_type) VALUES (?, ?, ?)';
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
      'iss',
      $data['driver_id'],
      $data['vehicle_number'],
      $data['vehicle_type']
    );
    return $stmt->execute();
  }

  // UPDATE VEHICLE
  public function updateVehicle($data)
  {
    $sql = 'UPDATE ' . $this->table . ' SET vehicle_number = ?, vehicle_type = ? WHERE driver_id = ?';
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
      'ssi',
      $data['vehicle_number'],
      $data['vehicle_type'],
      $data['driver_id']
    );
    return $stmt->execute();
  }

  // DELETE VEHICLE
  public function deleteVehicle($id)
  {
    $sql = 'DELETE FROM ' . $this->table . ' WHERE id = ?';
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }
}
?>
