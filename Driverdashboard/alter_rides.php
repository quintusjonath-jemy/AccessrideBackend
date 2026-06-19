<?php
include "db.php";

$conn->query("ALTER TABLE rides ADD COLUMN passenger_name VARCHAR(255)");
$conn->query("ALTER TABLE rides ADD COLUMN passenger_initials VARCHAR(10)");
$conn->query("ALTER TABLE rides ADD COLUMN trip_time VARCHAR(255)");

$dummy_names = ["Passenger A.", "Michael K.", "John S.", "Sarah M.", "David L."];
$dummy_initials = ["PA", "MK", "JS", "SM", "DL"];
$dummy_times = ["Today, 2:45 PM", "Yesterday", "2 days ago", "3 days ago", "4 days ago"];

$res = $conn->query("SELECT id FROM rides");
$index = 0;
while($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $name = $dummy_names[$index % 5];
    $initial = $dummy_initials[$index % 5];
    $time = $dummy_times[$index % 5];
    
    $stmt = $conn->prepare("UPDATE rides SET passenger_name=?, passenger_initials=?, trip_time=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $initial, $time, $id);
    $stmt->execute();
    $index++;
}

echo "Schema updated and data populated.\n";
?>
