
<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/EmergencyContact.php';

class DashboardController
{
  private $userModel;
  private $rideModel;
  private $contactModel;

  public function __construct($db)
  {
    $this->userModel = new User($db);
    $this->rideModel = new Ride($db);
    $this->contactModel = new EmergencyContact($db);
  }

  public function getDashboardData($userId)
  {
    $user = $this->userModel->getById($userId);

    if (!$user) {
      return [
        'success' => false,
        'message' => 'User not found'
      ];
    }

    return [
      'success' => true,
      'data' => [
        'user' => $user,
        'statistics' => [
          'total_rides' =>
            $this->rideModel->getTotalRides($userId)['total'],
          'completed_rides' =>
            $this->rideModel->getCompletedRides($userId)['total'],
          'pending_rides' =>
            $this->rideModel->getPendingRides($userId)['total'],
          'emergency_contacts' =>
            $this->contactModel->getCount($userId)['total']
        ],
        'upcoming_ride' =>
          $this->rideModel->getUpcomingRide($userId),
        'recent_rides' =>
          $this->rideModel->getRecentRides($userId)
      ]
    ];
  }
}
