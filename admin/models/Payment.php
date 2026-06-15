<?php

class Payment {
    private $conn;
    private $table = "payments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all payments, joining user and driver names (using fallback COALESCE to rides table)
    public function getPayments() {
        $sql = "SELECT 
                    p.id,
                    p.ride_id,
                    p.user_id,
                    p.driver_id,
                    p.amount,
                    p.payment_method,
                    p.status,
                    p.transaction_id,
                    p.created_at,
                    TRIM(CONCAT(COALESCE(u.first_name, ru.first_name, ''), ' ', COALESCE(u.last_name, ru.last_name, ''))) AS user_name,
                    TRIM(CONCAT(COALESCE(d.first_name, rd.first_name, ''), ' ', COALESCE(d.last_name, rd.last_name, ''))) AS driver_name
                FROM " . $this->table . " p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN drivers d ON p.driver_id = d.id
                LEFT JOIN rides r ON p.ride_id = r.id
                LEFT JOIN users ru ON r.user_id = ru.id
                LEFT JOIN drivers rd ON r.driver_id = rd.id
                ORDER BY p.created_at DESC";

        $result = $this->conn->query($sql);
        $payments = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['id'] = (int)$row['id'];
                $row['ride_id'] = (int)$row['ride_id'];
                $row['user_id'] = $row['user_id'] !== null ? (int)$row['user_id'] : null;
                $row['driver_id'] = $row['driver_id'] !== null ? (int)$row['driver_id'] : null;
                $row['amount'] = (float)$row['amount'];
                $payments[] = $row;
            }
        }
        return $payments;
    }

    // Update payment status
    public function updatePaymentStatus($id, $status) {
        $sql = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    // Get Payment Stats (Total earnings, pending cash, success rate, etc.)
    public function getPaymentStats() {
        // Total completed earnings
        $sqlEarnings = "SELECT SUM(amount) AS total FROM " . $this->table . " WHERE status = 'completed'";
        $resEarnings = $this->conn->query($sqlEarnings);
        $totalEarnings = 0.00;
        if ($resEarnings) {
            $row = $resEarnings->fetch_assoc();
            $totalEarnings = $row['total'] ? (float)$row['total'] : 0.00;
        }

        // Total pending amount
        $sqlPending = "SELECT SUM(amount) AS total FROM " . $this->table . " WHERE status = 'pending'";
        $resPending = $this->conn->query($sqlPending);
        $totalPending = 0.00;
        if ($resPending) {
            $row = $resPending->fetch_assoc();
            $totalPending = $row['total'] ? (float)$row['total'] : 0.00;
        }

        // Total payment count
        $sqlCount = "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS success_count FROM " . $this->table;
        $resCount = $this->conn->query($sqlCount);
        $totalCount = 0;
        $successCount = 0;
        if ($resCount) {
            $row = $resCount->fetch_assoc();
            $totalCount = (int)$row['total'];
            $successCount = (int)$row['success_count'];
        }

        $successRate = $totalCount > 0 ? round(($successCount / $totalCount) * 100, 1) : 100.0;

        return [
            "total_earnings" => $totalEarnings,
            "total_pending" => $totalPending,
            "success_rate" => $successRate,
            "total_transactions" => $totalCount
        ];
    }
}
?>
