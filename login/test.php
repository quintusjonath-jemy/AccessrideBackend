<?php

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=access_ride;charset=utf8mb4",
        "root",
        ""
    );

    echo "Database Connected Successfully";
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage();
}