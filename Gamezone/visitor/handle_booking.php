<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['visitor', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? 'create';
    $user_id = $_SESSION['user_id'];
    
    if ($action == 'create') {
        $room_id = $_POST['room_id'];
        $booking_date = $_POST['booking_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        // Check availability
        $check = $conn->prepare("SELECT booking_id FROM Booking 
            WHERE room_id = ? 
            AND booking_date = ? 
            AND status != 'cancelled'
            AND (
                (start_time < ? AND end_time > ?)
                OR (start_time < ? AND end_time > ?)
                OR (start_time >= ? AND end_time <= ?)
            )");
        $check->bind_param("isssssss", $room_id, $booking_date, $end_time, $start_time, $end_time, $start_time, $start_time, $end_time);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['booking_error'] = 'Room is not available for the selected date and time.';
            header('Location: dashboard.php?tab=rooms');
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status, booking_type) VALUES (?, ?, ?, ?, 'confirmed', 'regular')");
        $stmt->bind_param("isss", $room_id, $booking_date, $start_time, $end_time);
        $stmt->execute();
        
        $booking_id = $conn->insert_id;
        
        // Insert payment first
        $pay_stmt = $conn->prepare("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('pending', 0, CURDATE(), 'booking')");
        $pay_stmt->execute();
        $payment_id = $conn->insert_id;
        
        $stmt = $conn->prepare("INSERT INTO Makes (user_id, booking_id, payment_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $booking_id, $payment_id);
        $stmt->execute();
        
        $_SESSION['success'] = 'Room booked successfully!';
        header('Location: dashboard.php?tab=rooms');
        exit;
        
    } elseif ($action == 'cancel') {
        $booking_id = intval($_POST['booking_id']);
        
        // Only allow cancel if user owns the booking
        $check = $conn->query("SELECT m.booking_id FROM Makes m WHERE m.booking_id = $booking_id AND m.user_id = $user_id");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE Booking SET status = 'cancelled' WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $_SESSION['success'] = 'Booking cancelled successfully!';
        }
        header('Location: dashboard.php?tab=rooms');
        exit;
    }
}
?>