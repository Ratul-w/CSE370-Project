<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!file_exists('db.php')) {
        echo json_encode(['games' => [], 'error' => 'db.php not found']);
        exit;
    }

    include 'db.php';

    $room_id = intval($_GET['room_id'] ?? 0);
    $games = [];

    if ($room_id > 0) {
        // Check if column exists
        $col_check = $conn->query("SHOW COLUMNS FROM Room LIKE 'available_games'");
        if ($col_check->num_rows == 0) {
            // Column doesn't exist - create it
            $conn->query("ALTER TABLE Room ADD COLUMN available_games TEXT DEFAULT NULL");
        }
        
        $result = $conn->query("SELECT available_games FROM Room WHERE room_id = $room_id AND room_type = 'regular'");
        
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['available_games'])) {
                $games = array_map('trim', explode(',', $row['available_games']));
            }
        }
    }

    echo json_encode(['games' => $games, 'room_id' => $room_id]);
} catch (Exception $e) {
    echo json_encode(['games' => [], 'error' => $e->getMessage()]);
}
?>
