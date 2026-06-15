<?php
header("Content-Type: application/json");
include_once "../config/Database.php";
$db = (new Database())->connect();

$res = $db->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_row()) {
    $tables[] = $row[0];
}

$schemas = [];
foreach ($tables as $t) {
    $r = $db->query("DESCRIBE $t");
    $schemas[$t] = [];
    while ($row = $r->fetch_assoc()) {
        $schemas[$t][] = $row;
    }
}

echo json_encode($schemas, JSON_PRETTY_PRINT);
?>
