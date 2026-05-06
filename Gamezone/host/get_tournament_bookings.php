<?php
session_start();
include '../db.php';

$room_id = intval($_GET['room_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$room_id || !$date) {
    echo '<p class="text-muted">Select a room and date to see availability</p>';
    exit;
}

$bookings = $conn->query("
    SELECT t.tournament_id, t.t_name, t.status, t.start_date, t.end_date
    FROM Tournament t
    WHERE t.room_id = $room_id 
    AND t.start_date <= '$date'
    AND t.end_date >= '$date'
    AND t.status != 'cancelled'
    ORDER BY t.t_name
");

if ($bookings && $bookings->num_rows > 0) {
    echo '<div class="alert alert-danger p-2 mb-2">Room is NOT available on this date:</div>';
    echo '<table class="table table-bordered table-sm">';
    echo '<thead class="table-dark"><tr><th>Tournament</th><th>Status</th></tr></thead>';
    echo '<tbody>';
    while ($b = $bookings->fetch_assoc()) {
        $statusClass = $b['status'] == 'pending' ? 'bg-warning' : ($b['status'] == 'registration_open' ? 'bg-success' : 'bg-secondary');
        echo '<tr>';
        echo '<td>' . htmlspecialchars($b['t_name']) . '</td>';
        echo '<td><span class="badge ' . $statusClass . '">' . $b['status'] . '</span></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo '<p class="text-success p-2"><i class="bi bi-check-circle"></i> Room is available on this date!</p>';
}
?>