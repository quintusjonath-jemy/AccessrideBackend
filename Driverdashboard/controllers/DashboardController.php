<?php

require_once __DIR__ . '/../models/Driver.php';
require_once __DIR__ . '/../models/Ride.php';

class DashboardController
{
    private $driverModel;
    private $rideModel;

    public function __construct($db)
    {
        $this->driverModel = new Driver($db);
        $this->rideModel = new Ride($db);
    }

    public function getDashboardData($driverId)
    {
        $driver = $this->driverModel->getById($driverId);

        if (!$driver) {
            return [
                'success' => false,
                'message' => 'Driver not found'
            ];
        }

        $stats = $this->driverModel->getStats($driverId);
        $activeRide = $this->rideModel->getActiveRideForDriver($driverId);
        $latestPending = $this->rideModel->getLatestPendingRide($driverId);
        $recentRides = $this->rideModel->getRecentTrips($driverId);

        return [
            'success' => true,
            'data' => [
                'driver' => $driver,
                'statistics' => $stats,
                'active_ride' => $activeRide,
                'new_request' => $latestPending,
                'recent_rides' => $recentRides
            ]
        ];
    }

    public function updateStatus($driverId, $status)
    {
        return $this->driverModel->updateStatus($driverId, $status);
    }
}
