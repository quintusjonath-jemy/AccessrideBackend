<?php
include "db.php";
$result = $conn->query("DESCRIBE rides;");
while($row = $result->fetch_assoc()) { print_r($row); }
$result2 = $conn->query("DESCRIBE users;");
while($row = $result2->fetch_assoc()) { print_r($row); }
?>
