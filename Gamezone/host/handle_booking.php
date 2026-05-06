<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['host', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

// Add num_people column if not exists (ignore if already exists)
$columns = $conn->query("SHOW COLUMNS FROM Booking LIKE 'num_people'");
if ($columns->num_rows == 0) {
    $conn->query("ALTER TABLE Booking ADD COLUMN num_people INT DEFAULT 1");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create') {
        $room_id = $_POST['room_id'];
        $booking_date = $_POST['booking_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $user_id = $_SESSION['user_id'];
        $num_people = intval($_POST['num_people'] ?? 1);
        $max_per_player = 10;
        
        // Get room capacity
        $room_info = $conn->query("SELECT capacity FROM Room WHERE room_id = $room_id")->fetch_assoc();
        $room_capacity = intval($room_info['capacity'] ?? 0);
        
        if ($num_people > $room_capacity) {
            $_SESSION['booking_error'] = "Sorry, this room only has capacity for $room_capacity people.";
            header('Location: dashboard.php?tab=rooms');
            exit;
        }
        
        // Check total capacity used for this room/date/time
        $cap_check = $conn->query("
            SELECT COALESCE(SUM(b.num_people), 0) as used 
            FROM Booking b 
            WHERE b.room_id = $room_id 
            AND b.booking_date = '$booking_date' 
            AND b.status != 'cancelled'
            AND b.start_time < '$end_time' AND b.end_time > '$start_time'
        ");
        $cap_used = intval($cap_check->fetch_assoc()['used'] ?? 0);
        
        if ($cap_used + $num_people > $room_capacity) {
            $_SESSION['booking_error'] = "Sorry, room capacity ($room_capacity) is full for this slot. Currently used: $cap_used. Please choose different time.";
            header('Location: dashboard.php?tab=rooms');
            exit;
        }
        
        // Check player's current bookings for this room/date/time
        $player_check = $conn->query("
            SELECT COALESCE(SUM(b.num_people), 0) as player_used 
            FROM Booking b 
            JOIN Makes m ON b.booking_id = m.booking_id
            WHERE b.room_id = $room_id 
            AND b.booking_date = '$booking_date' 
            AND b.status != 'cancelled'
            AND b.start_time < '$end_time' AND b.end_time > '$start_time'
            AND m.user_id = $user_id
        ");
        $player_used = intval($player_check->fetch_assoc()['player_used'] ?? 0);
        
        if ($player_used + $num_people > $max_per_player) {
            $_SESSION['booking_error'] = "Sorry, you can only book maximum $max_per_player seats for this room/date/time. You have already booked $player_used seats.";
            header('Location: dashboard.php?tab=rooms');
            exit;
        }
        
        $game_name = $_POST['game_name'] ?? null;
        $stmt = $conn->prepare("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status, booking_type, game_name, num_people) VALUES (?, ?, ?, ?, 'confirmed', 'regular', ?, ?)");
        $stmt->bind_param("issssi", $room_id, $booking_date, $start_time, $end_time, $game_name, $num_people);
        $stmt->execute();
        
        $booking_id = $conn->insert_id;
        
        // Insert payment first, then link
        $pay_stmt = $conn->prepare("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('pending', 0, CURDATE(), 'booking')");
        $pay_stmt->execute();
        $payment_id = $conn->insert_id;
        
        $stmt = $conn->prepare("INSERT INTO Makes (user_id, booking_id, payment_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $booking_id, $payment_id);
        $stmt->execute();
        
        $_SESSION['success'] = 'Booking created successfully!';
        header('Location: dashboard.php?tab=rooms');
        exit;
    } elseif ($action == 'confirm') {
        $booking_id = $_POST['booking_id'];
        $stmt = $conn->prepare("UPDATE Booking SET status = 'confirmed' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
    } elseif ($action == 'cancel') {
        $booking_id = $_POST['booking_id'];
        // Delete from Makes first (foreign key)
        $conn->query("DELETE FROM Makes WHERE booking_id = $booking_id");
        // Delete from Booking
        $conn->query("DELETE FROM Booking WHERE booking_id = $booking_id");
    }
    
    header('Location: dashboard.php?tab=rooms');
    exit;
}
?>