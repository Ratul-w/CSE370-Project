<?php
session_start();
include '../db.php';

$room_id = intval($_GET['room_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$room_id || !$date) {
    echo '<p class="text-muted">Select a room and date to see available slots</p>';
    exit;
}

// Check for tournament bookings on this date
$tournament_bookings = $conn->query("
    SELECT t.t_name, t.start_date, t.end_date, t.status 
    FROM Tournament t
    WHERE t.room_id = $room_id 
    AND t.start_date <= '$date' 
    AND t.end_date >= '$date'
    AND t.status != 'cancelled'
");

if ($tournament_bookings && $tournament_bookings->num_rows > 0) {
    $t = $tournament_bookings->fetch_assoc();
    echo '<div class="alert alert-danger p-2 mb-2">Room is booked for tournament: ' . htmlspecialchars($t['t_name']) . '<br>';
    echo 'Tournament runs from ' . $t['start_date'] . ' to ' . $t['end_date'] . ' (Status: ' . $t['status'] . ')</div>';
} else {
    // Show regular bookings
    $bookings = $conn->query("
        SELECT start_time, end_time, status 
        FROM Booking 
        WHERE room_id = $room_id 
        AND booking_date = '$date'
        AND status != 'cancelled'
        ORDER BY start_time
    ");
    
    if ($bookings && $bookings->num_rows > 0) {
        echo '<table class="table table-bordered table-sm">';
        echo '<thead><tr><th>Booked Time Slots</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        while ($b = $bookings->fetch_assoc()) {
            $statusClass = $b['status'] == 'confirmed' ? 'bg-success' : 'bg-warning';
            echo '<tr>';
            echo '<td>' . $b['start_time'] . ' - ' . $b['end_time'] . '</td>';
            echo '<td><span class="badge ' . $statusClass . '">' . $b['status'] . '</span></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-success p-2"><i class="bi bi-check-circle"></i> Room is available all day!</p>';
    }
}
?>