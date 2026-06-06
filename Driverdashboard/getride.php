<?php
include "db.php";

$sql = "SELECT * FROM rides WHERE status='pending' LIMIT 1";
$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["message" => "No rides available"]);
}
?>