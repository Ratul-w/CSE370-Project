<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $tournament_id = intval($_POST['item_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $card_number = $_POST['card_number'] ?? '';
    
    if ($tournament_id == 0) {
        $_SESSION['error'] = 'Invalid tournament';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    // Validate card if there's a fee
    if ($amount > 0 && strlen($card_number) != 11) {
        $_SESSION['error'] = 'Enter 11-digit card number';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    $check = $conn->query("SELECT * FROM Participate WHERE tournament_id = $tournament_id AND user_id = $user_id");
    if ($check && $check->num_rows > 0) {
        $_SESSION['error'] = 'Already joined';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    $limit = $conn->query("SELECT player_limit, (SELECT COUNT(*) FROM Participate WHERE tournament_id = $tournament_id) as pc FROM Tournament WHERE tournament_id = $tournament_id")->fetch_assoc();
    if ($limit && $limit['pc'] >= $limit['player_limit']) {
        $_SESSION['error'] = 'Tournament full';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    $conn->query("INSERT INTO Participate (tournament_id, user_id, status) VALUES ($tournament_id, $user_id, 'registered')");
    
    // Process payment if entry fee > 0 - link via Makes table
    if ($amount > 0) {
        $conn->query("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', $amount, CURDATE(), 'tournament_entry')");
        $payment_id = $conn->insert_id;
        
        // Create booking for tournament if not exists
        $booking_chk = $conn->query("SELECT booking_id FROM Booking WHERE room_id = (SELECT room_id FROM Tournament WHERE tournament_id = $tournament_id) AND booking_type = 'tournament' LIMIT 1");
        if ($booking_chk && $booking_chk->num_rows > 0) {
            $booking_id = $booking_chk->fetch_assoc()['booking_id'];
        } else {
            $conn->query("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status, booking_type) 
                SELECT room_id, start_date, '00:00:00', '23:59:59', 'confirmed', 'tournament' 
                FROM Tournament WHERE tournament_id = $tournament_id");
            $booking_id = $conn->insert_id;
        }
        
        // Link payment to user via Makes
        $conn->query("INSERT INTO Makes (user_id, booking_id, payment_id) VALUES ($user_id, $booking_id, $payment_id)");
    }
    
    $conn->query("UPDATE Tournament SET status = 'registration_open' WHERE tournament_id = $tournament_id");
    
    $_SESSION['success'] = 'Joined tournament! Entry fee: ' . number_format($amount, 0) . ' BDT';
    header('Location: dashboard.php?tab=tournaments');
    exit;
}
?>