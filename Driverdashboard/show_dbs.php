<?php
$conn = new mysqli("localhost", "root", "");
$res = $conn->query("SHOW DATABASES");
while($row = $res->fetch_array()){
    echo $row[0] . "\n";
}
?>
