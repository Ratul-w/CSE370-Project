<?php
include 'db.php';

echo "Checking rooms and their games:\n";

$result = $conn->query("SELECT room_id, room_name, available_games FROM Room WHERE room_type = 'regular'");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Room #{$row['room_id']}: {$row['room_name']}\n";
        echo "  Games: " . ($row['available_games'] ?: '--- NO GAMES ASSIGNED ---') . "\n";
    }
}
?>