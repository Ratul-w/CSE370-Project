<?php
session_start();
include '../db.php';

$room_id = intval($_GET['room_id'] ?? 0);
$date = $_GET['date'] ?? '';

if ($room_id && $date) {
    $check = $conn->query("SELECT t_name FROM Tournament WHERE room_id = $room_id AND start_date = '$date' AND status != 'completed'");
    if ($check && $check->num_rows > 0) {
        echo '<span class="text-danger">Already booked!</span>';
    } else {
        echo '<span class="text-success">Available</span>';
    }
}
?>