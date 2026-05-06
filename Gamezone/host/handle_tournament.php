<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['host', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    if ($action == 'create') {
        $t_name = $_POST['t_name'];
        $entry_fee = floatval($_POST['entry_fee'] ?? 0);
        $prize_money = floatval($_POST['prize_money'] ?? 0);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'] ?? $start_date;
        $room_id = !empty($_POST['room_id']) ? intval($_POST['room_id']) : null;
        $visitor_limit = intval($_POST['visitor_limit'] ?? 50);
        $player_limit = intval($_POST['player_limit'] ?? 16);
        
        // Check for required fields
        if (empty($t_name) || empty($start_date)) {
            $_SESSION['booking_error'] = 'Please fill in all required fields.';
            header('Location: dashboard.php?tab=tournaments');
            exit;
        }
        
        // Check limits
        if ($visitor_limit < 2 || $player_limit < 2) {
            $_SESSION['booking_error'] = 'At least 2 players and 2 visitors required.';
            header('Location: dashboard.php?tab=tournaments');
            exit;
        }
        
        // Check room capacity
        if ($room_id !== null) {
            $roomCheck = $conn->query("SELECT capacity FROM Room WHERE room_id = $room_id")->fetch_assoc();
            $roomCapacity = intval($roomCheck['capacity'] ?? 0);
            $max_participants = $visitor_limit + $player_limit;
            if ($max_participants > $roomCapacity) {
                $_SESSION['booking_error'] = 'Total participants (' . $max_participants . ') exceeds room capacity (' . $roomCapacity . ').';
                header('Location: dashboard.php?tab=tournaments');
                exit;
            }
            
            // Check for existing tournaments with overlapping date range
            $checkOverlap = $conn->query("
                SELECT t_name, start_date, end_date 
                FROM Tournament 
                WHERE room_id = $room_id 
                AND status != 'completed' 
                AND start_date <= '$end_date' 
                AND end_date >= '$start_date'
            ");
            if ($checkOverlap && $checkOverlap->num_rows > 0) {
                $existing = $checkOverlap->fetch_assoc();
                $_SESSION['booking_error'] = 'Room already booked for tournament "' . htmlspecialchars($existing['t_name']) . '" from ' . date('F d, Y', strtotime($existing['start_date'])) . ' to ' . date('F d, Y', strtotime($existing['end_date'])) . '. Please choose different dates.';
                header('Location: dashboard.php?tab=tournaments');
                exit;
            }
        }
        
        // Create tournament (match exact DB columns)
        if ($room_id === null) {
            $sql = "INSERT INTO Tournament (t_name, max_participants, entry_fee, prize_money, start_date, end_date, status, user_id, visitor_limit, player_limit) 
                   VALUES ('$t_name', $player_limit, $entry_fee, $prize_money, '$start_date', '$end_date', 'registration_open', $user_id, $visitor_limit, $player_limit)";
        } else {
            $sql = "INSERT INTO Tournament (t_name, max_participants, entry_fee, prize_money, start_date, end_date, status, room_id, user_id, visitor_limit, player_limit) 
                   VALUES ('$t_name', $player_limit, $entry_fee, $prize_money, '$start_date', '$end_date', 'registration_open', $room_id, $user_id, $visitor_limit, $player_limit)";
        }
        
        if ($conn->query($sql)) {
            $tournament_id = $conn->insert_id;
            
            // Create Booking records for each day in the date range
            if ($room_id !== null) {
                $start = new DateTime($start_date);
                $end = new DateTime($end_date);
                $end->modify('+1 day'); // Include end date
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
                foreach ($period as $date) {
                    $current_date = $date->format('Y-m-d');
                    // Insert Booking for this day (full day booking)
                    $booking_sql = "INSERT INTO Booking (room_id, booking_date, start_time, end_time, status) 
                                    VALUES ($room_id, '$current_date', '00:00:00', '23:59:59', 'confirmed')";
                    if ($conn->query($booking_sql)) {
                        $booking_id = $conn->insert_id;
                        // Link booking to host user via Makes table
                        $conn->query("INSERT INTO Makes (user_id, booking_id) VALUES ($user_id, $booking_id)");
                    }
                }
            }
            
            $_SESSION['success'] = 'Tournament created successfully!';
        } else {
            $_SESSION['booking_error'] = 'Error: ' . $conn->error;
        }
        
        header('Location: dashboard.php?tab=tournaments');
        exit;
        
    } elseif ($action == 'delete') {
        $tournament_id = intval($_POST['tournament_id']);
        
        if ($_SESSION['role'] === 'admin') {
            $conn->query("DELETE FROM Tournament WHERE tournament_id = $tournament_id");
        } else {
            $conn->query("DELETE FROM Tournament WHERE tournament_id = $tournament_id AND user_id = $user_id");
        }
        
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
}
?>