<?php
$conn = new mysqli("localhost", "root", "", "accessride");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>