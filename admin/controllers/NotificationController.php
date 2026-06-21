<?php

include_once '../config/Database.php';
include_once '../models/Notification.php';

class NotificationController
{
  private $notification;

  public function __construct()
  {
    $database = new Database();
    $db = $database->connect();
    $this->notification = new Notification($db);
  }

  // GET ALL NOTIFICATIONS
  public function index()
  {
    echo json_encode(
      $this->notification->getNotifications()
    );
  }

  // MARK SPECIFIC NOTIFICATION AS READ
  public function markAsRead($id)
  {
    $success = $this->notification->markAsRead($id);
    echo json_encode([
      'success' => $success
    ]);
  }

  // MARK ALL NOTIFICATIONS AS READ
  public function markAllAsRead()
  {
    $success = $this->notification->markAllAsRead();
    echo json_encode([
      'success' => $success
    ]);
  }

  // DELETE NOTIFICATION
  public function destroy($id)
  {
    $success = $this->notification->deleteNotification($id);
    echo json_encode([
      'success' => $success
    ]);
  }
}
?>
