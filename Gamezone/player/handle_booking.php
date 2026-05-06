<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'visitor', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? 'create';
    
    if ($action == 'cancel') {
        $booking_id = intval($_POST['booking_id']);
        
        // Verify ownership before cancel
        $check = $conn->query("SELECT m.booking_id FROM Makes m WHERE m.booking_id = $booking_id AND m.user_id = $user_id");
        if ($check && $check->num_rows > 0) {
            // Get num_people before cancelling
            $num = $conn->query("SELECT num_people FROM Booking WHERE booking_id = $booking_id")->fetch_assoc();
            $people = intval($num['num_people'] ?? 1);
            
            $conn->query("UPDATE Booking SET status = 'cancelled' WHERE booking_id = $booking_id");
            $_SESSION['success'] = 'Booking cancelled! Seats released.';
        }
        header('Location: dashboard.php?tab=rooms');
        exit;
    }
    
    // Create new booking (1 seat per player)
    $room_id = intval($_POST['room_id']);
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $game_name = $_POST['game_name'] ?? '';
    $num_people = 1; // Each player books 1 seat
    
    if (!$room_id || !$booking_date || !$start_time || !$end_time) {
        $_SESSION['error'] = 'Fill all fields';
        header('Location: dashboard.php?tab=rooms');
        exit;
    }
    
    // Get room capacity
    $room = $conn->query("SELECT capacity FROM Room WHERE room_id = $room_id")->fetch_assoc();
    $room_capacity = intval($room['capacity'] ?? 0);
    
    // Add num_people column if not exists
    $conn->query("ALTER TABLE Booking ADD COLUMN IF NOT EXISTS num_people INT DEFAULT 1");
    
    // Check total seats already booked for this room/date/time
    $used = $conn->query("
        SELECT COALESCE(SUM(num_people), 0) as used 
        FROM Booking 
        WHERE room_id = $room_id 
        AND booking_date = '$booking_date' 
        AND status != 'cancelled' 
        AND start_time < '$end_time' 
        AND end_time > '$start_time'
    ")->fetch_assoc();
    
    $used_seats = intval($used['used']);
    $available_seats = $room_capacity - $used_seats;
    
    if ($available_seats < 1) {
        $_SESSION['error'] = "Room full! Already $used_seats/$room_capacity seats booked for this time. Try different time.";
        header('Location: dashboard.php?tab=rooms');
        exit;
    }
    
    // Insert booking with 1 seat
    $conn->query("ALTER TABLE Booking ADD COLUMN IF NOT EXISTS game_name VARCHAR(100)");
    
    if ($game_name) {
        $conn->query("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status, booking_type, game_name, num_people) VALUES ($room_id, '$booking_date', '$start_time', '$end_time', 'confirmed', 'regular', '$game_name', $num_people)");
    } else {
        $conn->query("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status, booking_type, num_people) VALUES ($room_id, '$booking_date', '$start_time', '$end_time', 'confirmed', 'regular', $num_people)");
    }
    $booking_id = $conn->insert_id;
    
    $conn->query("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('pending', 0, CURDATE(), 'booking')");
    $payment_id = $conn->insert_id;
    
    $conn->query("INSERT INTO Makes (user_id, booking_id, payment_id) VALUES ($user_id, $booking_id, $payment_id)");
    
    $_SESSION['success'] = "Room booked! 1 seat reserved. ($used_seats + 1)/$room_capacity seats filled.";
    header('Location: dashboard.php?tab=rooms');
    exit;
}
?>