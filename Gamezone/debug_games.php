<?php
include 'db.php';

echo "=== CHECKING SETUP ===\n\n";

// Check admin table
echo "1. ADMIN TABLE:\n";
$result = $conn->query("DESCRIBE admin");
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']}: {$row['Type']}\n";
}

// Check which admin has games
echo "\n2. ADMIN GAMES:\n";
$admin = $conn->query("SELECT * FROM admin LIMIT 1");
if ($admin && $row = $admin->fetch_assoc()) {
    echo "  available_games: " . ($row['available_games'] ?: 'NULL/EMPTY') . "\n";
} else {
    echo "  No admin record found!\n";
}

// Check Room available_games
echo "\n3. ROOM GAMES:\n";
$result = $conn->query("SELECT room_id, room_name, available_games FROM Room WHERE room_type = 'regular'");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  Room {$row['room_id']}: {$row['room_name']} = " . ($row['available_games'] ?: '---') . "\n";
    }
}
?>