<?php
session_start();
include '../db.php';

$room_id = intval($_GET['room_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$room_id || !$date) {
    echo '<p class="text-muted">Select a room and date to see available slots</p>';
    exit;
}

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
    echo '<p class="text-success"><i class="bi bi-check-circle"></i> Room is available all day! No bookings yet.</p>';
}
?>