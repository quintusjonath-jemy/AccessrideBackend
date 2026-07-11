<?php

class Driver
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getById($driverId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.id, d.first_name, d.last_name, d.email, d.phone, d.status, d.profile_image, d.created_at,
                v.vehicle_number, v.vehicle_type, v.vehicle_brand, v.vehicle_model, v.vehicle_color, v.year_manufacture
            FROM drivers d
            LEFT JOIN vehicles v ON d.id = v.driver_id
            WHERE d.id = ?
        ");
        $stmt->bind_param("i", $driverId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateStatus($driverId, $status)
    {
        $stmt = $this->db->prepare("UPDATE drivers SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $driverId);
        return $stmt->execute();
    }

    public function getStats($driverId)
    {
        $stats = [
            'rating' => 4.8, // Default rating
            'total_trips' => 0,
            'today_earnings' => 0.00,
            'today_trips' => 0,
            'weekly_earnings' => 0.00,
            'weekly_trips' => 0,
            'current_month_earnings' => 0.00,
            'prev_month_earnings' => 0.00,
            'subscription_expires_at' => 'No Subscription'
        ];

        // 1. Get total trips
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM rides WHERE driver_id = ? AND status = 'completed'");
        $stmt->bind_param("i", $driverId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stats['total_trips'] = intval($res['total'] ?? 0);

        // 2. Get today's trips and earnings
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, SUM(fare) as earnings 
            FROM rides 
            WHERE driver_id = ? 
            AND status = 'completed' 
            AND ride_date BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $driverId, $todayStart, $todayEnd);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        $stats['today_trips'] = intval($res['total'] ?? 0);
        $stats['today_earnings'] = floatval($res['earnings'] ?? 0.00);

        // 2.5. Get weekly trips and earnings
        $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $weekEnd = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, SUM(fare) as earnings 
            FROM rides 
            WHERE driver_id = ? 
            AND status = 'completed' 
            AND ride_date BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $driverId, $weekStart, $weekEnd);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stats['weekly_trips'] = intval($res['total'] ?? 0);
        $stats['weekly_earnings'] = floatval($res['earnings'] ?? 0.00);

        // 3. Get current month earnings
        $currentMonthStart = date('Y-m-01 00:00:00');
        $currentMonthEnd = date('Y-m-t 23:59:59');
        $stmt = $this->db->prepare("
            SELECT SUM(fare) as earnings 
            FROM rides 
            WHERE driver_id = ? 
            AND status = 'completed' 
            AND ride_date BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $driverId, $currentMonthStart, $currentMonthEnd);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stats['current_month_earnings'] = floatval($res['earnings'] ?? 0.00);

        // 4. Get previous month earnings
        $prevMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
        $prevMonthEnd = date('Y-m-t 23:59:59', strtotime('last day of last month'));
        $stmt = $this->db->prepare("
            SELECT SUM(fare) as earnings 
            FROM rides 
            WHERE driver_id = ? 
            AND status = 'completed' 
            AND ride_date BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $driverId, $prevMonthStart, $prevMonthEnd);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stats['prev_month_earnings'] = floatval($res['earnings'] ?? 0.00);

        // 5. Get subscription status and expiry date
        $stats['subscription_status'] = 'No Active Plan';
        $stats['subscription_expires_at'] = 'No Active Plan';
        
        $stmt = $this->db->prepare("
            SELECT status, expires_at 
            FROM subscriptions 
            WHERE driver_id = ? 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $driverId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $stats['subscription_status'] = $res['status'] ?? 'expired';
            if (!empty($res['expires_at'])) {
                $stats['subscription_expires_at'] = date('Y-m-d', strtotime($res['expires_at']));
                // If the expiration timestamp is in the past, force status to expired
                if (strtotime($res['expires_at']) < time()) {
                    $stats['subscription_status'] = 'expired';
                }
            }
        }

        return $stats;
    }
}
