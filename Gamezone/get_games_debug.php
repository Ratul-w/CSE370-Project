<?php
header('Content-Type: text/plain');

echo "get_games_debug.php called\n";

if (!file_exists('db.php')) {
    echo "ERROR: db.php not found\n";
    exit;
}

echo "Including db.php...\n";
include 'db.php';

echo "db.php included successfully\n";

$room_id = intval($_GET['room_id'] ?? 0);
echo "room_id: $room_id\n";

if ($room_id > 0) {
    echo "Querying Room table...\n";
    $stmt = $conn->prepare("SELECT available_games, room_type FROM Room WHERE room_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "Found row: " . print_r($row, true) . "\n";
            if (!empty($row['available_games'])) {
                $games = array_map('trim', explode(',', $row['available_games']));
                echo "Games: " . print_r($games, true) . "\n";
            } else {
                echo "available_games is empty\n";
            }
        } else {
            echo "No row found for room_id $room_id\n";
        }
    } else {
        echo "ERROR: Failed to prepare statement\n";
    }
} else {
    echo "No room_id provided\n";
}
?>