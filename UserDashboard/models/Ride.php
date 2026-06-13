<?php

class Ride
{
    private $conn;
    private $table = "rides";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getTotalRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM rides
             WHERE user_id = ?"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getCompletedRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM rides
             WHERE user_id = ?
             AND status = 'completed'"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getPendingRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM rides
             WHERE user_id = ?
             AND status = 'pending'"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getUpcomingRide($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
            FROM rides
            WHERE user_id = ?
            AND ride_date >= NOW()
            AND status IN ('pending','accepted')
            ORDER BY ride_date ASC
            LIMIT 1"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getRecentRides($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM rides
             WHERE user_id = ?
             ORDER BY ride_date DESC
             LIMIT 5"
        );

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}