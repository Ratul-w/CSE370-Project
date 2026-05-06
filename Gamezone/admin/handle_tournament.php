<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create') {
        $t_name = $_POST['t_name'];
        $entry_fee = floatval($_POST['entry_fee'] ?? 0);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ?? $start_date;
        $room_id = !empty($_POST['room_id']) ? intval($_POST['room_id']) : null;
        $visitor_limit = intval($_POST['visitor_limit'] ?? 50);
        $player_limit = intval($_POST['player_limit'] ?? 16);
        $max_participants = $visitor_limit + $player_limit;
        
        // Check room capacity
        if ($room_id !== null) {
            $roomCheck = $conn->query("SELECT capacity FROM Room WHERE room_id = $room_id")->fetch_assoc();
            $roomCapacity = intval($roomCheck['capacity'] ?? 0);
            if ($max_participants > $roomCapacity) {
                $_SESSION['booking_error'] = 'Total participants (' . $max_participants . ') exceeds room capacity (' . $roomCapacity . ').';
                header('Location: dashboard.php?tab=tournaments');
                exit;
            }
        }
        
        if ($room_id === null) {
            $stmt = $conn->prepare("INSERT INTO Tournament (t_name, max_participants, entry_fee, start_date, end_date, status, visitor_limit, player_limit) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)");
            $stmt->bind_param("sidssii", $t_name, $max_participants, $entry_fee, $start_date, $end_date, $visitor_limit, $player_limit);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO Tournament (t_name, max_participants, entry_fee, start_date, end_date, status, room_id, visitor_limit, player_limit) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
            $stmt->bind_param("sidssiii", $t_name, $max_participants, $entry_fee, $start_date, $end_date, $room_id, $visitor_limit, $player_limit);
            $stmt->execute();
            
            // Create booking in Booking table
            $booking_stmt = $conn->prepare("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status, booking_type) VALUES (?, ?, '00:00:00', '23:59:59', 'confirmed', 'tournament')");
            $booking_stmt->bind_param("is", $room_id, $start_date);
            $booking_stmt->execute();
            
            $booking_id = $conn->insert_id;
        }
    } elseif ($action == 'delete') {
        $tournament_id = intval($_POST['tournament_id']);
        $stmt = $conn->prepare("DELETE FROM Tournament WHERE tournament_id = ?");
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
    }
    
    header('Location: dashboard.php?tab=tournaments');
    exit;
}
?>
