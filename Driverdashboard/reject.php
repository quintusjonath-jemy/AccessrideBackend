<?php
include "db.php";

$sql = "UPDATE rides SET status='accepted' WHERE status='pending' LIMIT 1";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "Ride Accepted"]);
} else {
    echo json_encode(["message" => "Error"]);
}
?>