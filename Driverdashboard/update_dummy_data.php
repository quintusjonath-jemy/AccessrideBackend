<?php
include "db.php";

$conn->query("DELETE FROM rides WHERE passenger_name IN ('Jeevan.A', 'Michael K.', 'John S.')");

$trips = [
    [
        'initials' => 'PA',
        'name' => 'Jeevan.A',
        'time' => 'Today, 2:45 PM',
        'amount' => 100.00
    ],
    [
        'initials' => 'MK',
        'name' => 'Michael K.',
        'time' => 'Yesterday',
        'amount' => 250.00
    ],
    [
        'initials' => 'JS',
        'name' => 'John S.',
        'time' => '2 days ago',
        'amount' => 150.00
    ]
];

foreach (array_reverse($trips) as $trip) {
    $stmt = $conn->prepare("INSERT INTO rides (passenger_initials, passenger_name, trip_time, fare, status) VALUES (?, ?, ?, ?, 'completed')");
    $stmt->bind_param("sssd", $trip['initials'], $trip['name'], $trip['time'], $trip['amount']);
    $stmt->execute();
}

echo "Database updated with new trips.\n";
?>
