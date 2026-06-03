<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$date = date("Y-m-d H:i:s");

file_put_contents("sos_log.txt", "SOS at $date\n", FILE_APPEND);

echo json_encode([
  "status" => "success",
  "message" => "SOS sent successfully"
]);
?>cd