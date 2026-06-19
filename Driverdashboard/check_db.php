<?php
include "db.php";
echo "TABLES:\n";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) { echo $row[0] . "\n"; }

echo "\nRIDES COLUMNS:\n";
$res = $conn->query("DESCRIBE rides");
while($row = $res->fetch_assoc()) { echo $row['Field'] . "\n"; }

echo "\nUSERS COLUMNS (if exists):\n";
$res = $conn->query("DESCRIBE users");
if($res) {
    while($row = $res->fetch_assoc()) { echo $row['Field'] . "\n"; }
} else { echo "No users table\n"; }
?>
