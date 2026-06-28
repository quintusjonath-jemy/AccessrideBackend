<?php

include_once '../config/Database.php';
include_once '../models/Payment.php';

class PaymentController
{
  private $payment;

  public function __construct()
  {
    $database = new Database();
    $db = $database->connect();
    $this->payment = new Payment($db);
  }

  // Get payments and statistics
  public function index()
  {
    $payments = $this->payment->getPayments();
    $stats = $this->payment->getPaymentStats();

    echo json_encode([
      'success' => true,
      'payments' => $payments,
      'stats' => $stats
    ]);
  }

  // Update payment status
  public function update($data)
  {
    if (!isset($data['id']) || !isset($data['status'])) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'message' => 'Payment ID and Status are required'
      ]);
      return;
    }

    $id = (int) $data['id'];
    $status = trim($data['status']);
    $allowedStatuses = ['pending', 'completed', 'failed', 'refunded'];

    if (!in_array($status, $allowedStatuses)) {
      http_response_code(400);
      echo json_encode([
        'success' => false,
        'message' => 'Invalid payment status'
      ]);
      return;
    }

    $success = $this->payment->updatePaymentStatus($id, $status);
    echo json_encode([
      'success' => $success,
      'message' => $success ? 'Payment status updated successfully' : 'Failed to update payment status'
    ]);
  }
}
?>
