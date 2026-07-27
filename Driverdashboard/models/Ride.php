<?php

class Ride
{
    // UML Class Diagram Attributes (Private)
    private $rideid;
    private $pickupLocation;
    private $destination;
    private $date;
    private $status;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getLatestPendingRide($driverId = null)
    {
        if ($driverId) {
            $stmt = $this->db->prepare("
                SELECT 
                    rides.id, 
                    rides.user_id, 
                    rides.pickup_location as pickup, 
                    rides.dropoff_location as dropoff, 
                    rides.distance_km as distance, 
                    rides.fare,
                    CONCAT(users.first_name, ' ', users.last_name) AS passenger_name
                FROM ride_requests rr
                JOIN rides ON rr.ride_id = rides.id
                LEFT JOIN users ON rides.user_id = users.id
                WHERE rr.driver_id = ? 
                AND rr.driver_status = 'pending'
                AND rides.status = 'pending'
                AND EXISTS (
                    SELECT 1 FROM subscriptions s
                    WHERE s.driver_id = rr.driver_id
                    AND s.status = 'active'
                    AND s.expires_at > NOW()
                )
                ORDER BY rr.id DESC 
                LIMIT 1
            ");
            $stmt->bind_param("i", $driverId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        $sql = "
            SELECT 
                rides.id, 
                rides.user_id, 
                rides.pickup_location as pickup, 
                rides.dropoff_location as dropoff, 
                rides.distance_km as distance, 
                rides.fare,
                CONCAT(users.first_name, ' ', users.last_name) AS passenger_name
            FROM rides 
            LEFT JOIN users ON rides.user_id = users.id
            WHERE rides.status = 'pending' 
            ORDER BY rides.id DESC 
            LIMIT 1
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function getActiveRideForDriver($driverId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                rides.id, 
                rides.pickup_location as pickup, 
                rides.dropoff_location as dropoff, 
                rides.fare,
                rides.distance_km as distance,
                rides.status,
                CONCAT(users.first_name, ' ', users.last_name) AS passenger_name,
                users.phone as passenger_phone,
                drivers.latitude as driver_lat,
                drivers.longitude as driver_lng
            FROM rides 
            LEFT JOIN users ON rides.user_id = users.id
            LEFT JOIN drivers ON rides.driver_id = drivers.id
            WHERE rides.driver_id = ? 
            AND (rides.status = 'accepted' OR rides.status = 'active')
            ORDER BY rides.id DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $driverId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRecentTrips($driverId = null)
    {
        if ($driverId) {
            $stmt = $this->db->prepare("
                SELECT 
                    rides.id, 
                    rides.fare, 
                    rides.status, 
                    rides.ride_date,
                    rides.pickup_location,
                    rides.dropoff_location,
                    CONCAT(users.first_name, ' ', users.last_name) AS passenger_name,
                    CONCAT(SUBSTRING(users.first_name, 1, 1), SUBSTRING(users.last_name, 1, 1)) AS passenger_initials
                FROM rides 
                LEFT JOIN users ON rides.user_id = users.id
                WHERE (rides.driver_id = ?)
                AND (rides.status = 'completed')
                ORDER BY rides.id DESC 
                LIMIT 5
            ");
            $stmt->bind_param("i", $driverId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "
                SELECT 
                    rides.id, 
                    rides.fare, 
                    rides.status, 
                    rides.ride_date,
                    rides.pickup_location,
                    rides.dropoff_location,
                    CONCAT(users.first_name, ' ', users.last_name) AS passenger_name,
                    CONCAT(SUBSTRING(users.first_name, 1, 1), SUBSTRING(users.last_name, 1, 1)) AS passenger_initials
                FROM rides 
                LEFT JOIN users ON rides.user_id = users.id
                WHERE rides.status = 'completed'
                ORDER BY rides.id DESC 
                LIMIT 5
            ";
            $result = $this->db->query($sql);
        }

        $trips = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $trips[] = $row;
            }
        }
        return $trips;
    }

    public function acceptRide($driverId, $rideId)
    {
        // 1. Update rides table status to accepted
        $stmt = $this->db->prepare("UPDATE rides SET status = 'accepted', driver_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $driverId, $rideId);
        $res1 = $stmt->execute();

        // 2. Update ride_requests table status to accepted and record accepted_at timestamp
        $stmt2 = $this->db->prepare("UPDATE ride_requests SET driver_status = 'accepted', accepted_at = NOW() WHERE ride_id = ? AND driver_id = ?");
        $stmt2->bind_param("ii", $rideId, $driverId);
        $res2 = $stmt2->execute();

        // 3. Notify the passenger that their ride was accepted
        if ($res1 && $res2) {
            $stmtUser = $this->db->prepare("SELECT user_id FROM rides WHERE id = ?");
            $stmtUser->bind_param("i", $rideId);
            $stmtUser->execute();
            $row = $stmtUser->get_result()->fetch_assoc();
            if ($row && !empty($row['user_id'])) {
                $userId = (int)$row['user_id'];
                $stmtNotif = $this->db->prepare("INSERT INTO user_notifications (user_id, title, message, type) VALUES (?, 'Driver Accepted', 'A driver has accepted your ride request and is on the way to your pickup location.', 'ride')");
                $stmtNotif->bind_param("i", $userId);
                $stmtNotif->execute();
                $stmtNotif->close();
            }
            $stmtUser->close();
        }

        return $res1 && $res2;
    }

    public function cancelRide($rideId)
    {
        // Notify the passenger before cancelling
        $stmtUser = $this->db->prepare("SELECT user_id FROM rides WHERE id = ?");
        $stmtUser->bind_param("i", $rideId);
        $stmtUser->execute();
        $row = $stmtUser->get_result()->fetch_assoc();
        if ($row && !empty($row['user_id'])) {
            $userId = (int)$row['user_id'];
            $stmtNotif = $this->db->prepare("INSERT INTO user_notifications (user_id, title, message, type) VALUES (?, 'Ride Cancelled by Driver', 'Unfortunately your driver has cancelled this ride. A new driver will be assigned shortly.', 'warning')");
            $stmtNotif->bind_param("i", $userId);
            $stmtNotif->execute();
            $stmtNotif->close();
        }
        $stmtUser->close();

        $stmt = $this->db->prepare("UPDATE rides SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $rideId);
        return $stmt->execute();
    }

    public function rejectAndReassign($rideId, $currentDriverId)
    {
        // 1. Update the request status to rejected in ride_requests
        $stmt = $this->db->prepare("UPDATE ride_requests SET driver_status = 'rejected' WHERE ride_id = ? AND driver_id = ?");
        $stmt->bind_param("ii", $rideId, $currentDriverId);
        $stmt->execute();

        // 2. Get ride details
        $stmt = $this->db->prepare("SELECT user_id, pickup_location, vehicle_type FROM rides WHERE id = ?");
        $stmt->bind_param("i", $rideId);
        $stmt->execute();
        $ride = $stmt->get_result()->fetch_assoc();

        if (!$ride) {
            return false;
        }

        $userId = $ride['user_id'];
        $pickup = $ride['pickup_location'];
        $vehicleType = $ride['vehicle_type'];

        // 3. Geocode pickup location name using Mapbox geocoding
        $lat = null;
        $lng = null;
        $mapboxToken = getenv('MAPBOX_TOKEN') ?: '';

        if (!empty($mapboxToken)) {
            $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . urlencode($pickup) . ".json?access_token=" . $mapboxToken . "&limit=1";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data['features'][0]['geometry']['coordinates'])) {
                    $lng = $data['features'][0]['geometry']['coordinates'][0];
                    $lat = $data['features'][0]['geometry']['coordinates'][1];
                }
            }
        } // end if (!empty($mapboxToken))

        // If geocoding failed or token missing, default to Colombo center
        if ($lat === null || $lng === null) {
            $lat = 6.9271;
            $lng = 79.8612;
        }

        // 4. Find all drivers who already rejected this ride
        $rejectedDrivers = [];
        $stmt = $this->db->prepare("SELECT driver_id FROM ride_requests WHERE ride_id = ? AND driver_status = 'rejected'");
        $stmt->bind_param("i", $rideId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rejectedDrivers[] = (int)$row['driver_id'];
        }

        // 5. Query next nearest driver who has NOT rejected and is online
        $vehicleFilter = "";
        if ($vehicleType) {
            $vehicleFilter = "AND id IN (SELECT driver_id FROM vehicles WHERE LOWER(vehicle_type) = LOWER(?))";
        }

        $excludeFilter = "";
        if (!empty($rejectedDrivers)) {
            $excludeFilter = "AND id NOT IN (" . implode(",", $rejectedDrivers) . ")";
        }

        // Only pick drivers with an active, non-expired subscription
        $subscriptionFilter = "AND id IN (
            SELECT driver_id FROM subscriptions
            WHERE status = 'active' AND expires_at > NOW()
        )";

        $sql = "
            SELECT id,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
            FROM drivers
            WHERE status = 'online'
            AND latitude IS NOT NULL
            AND longitude IS NOT NULL
            {$excludeFilter}
            {$subscriptionFilter}
            {$vehicleFilter}
            ORDER BY distance ASC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt) {
            if ($vehicleType) {
                $stmt->bind_param('ddds', $lat, $lng, $lat, $vehicleType);
            } else {
                $stmt->bind_param('ddd', $lat, $lng, $lat);
            }
            $stmt->execute();
            $nextDriver = $stmt->get_result()->fetch_assoc();
            
            if ($nextDriver) {
                $nextDriverId = (int)$nextDriver['id'];
                
                // Update rides table with the next driver_id
                $updateStmt = $this->db->prepare("UPDATE rides SET driver_id = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $nextDriverId, $rideId);
                $updateStmt->execute();

                // Create new request record in ride_requests
                $requestStmt = $this->db->prepare("INSERT INTO ride_requests (user_id, driver_id, ride_id, user_status, driver_status) VALUES (?, ?, ?, 'pending', 'pending')");
                $requestStmt->bind_param("iii", $userId, $nextDriverId, $rideId);
                $requestStmt->execute();

                return $nextDriverId;
            }
        }

        // If no more drivers, set ride to cancelled
        $updateStmt = $this->db->prepare("UPDATE rides SET status = 'cancelled' WHERE id = ?");
        $updateStmt->bind_param("i", $rideId);
        $updateStmt->execute();

        return false;
    }
}
