<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['visitor', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $tournament_id = $_POST['tournament_id'] ?? $_POST['item_id'] ?? 0;
    $entry_fee = floatval($_POST['amount'] ?? 0);
    $card_number = intval($_POST['card_number'] ?? 0);
    
    if ($tournament_id == 0) {
        $_SESSION['error'] = 'Invalid tournament.';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    if ($card_number > 0 && ($card_number < 10000000000 || $card_number > 99999999999)) {
        $_SESSION['error'] = 'Please enter a valid 11-digit credit card number.';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    // Check if this specific user already joined
    $check = $conn->query("SELECT * FROM visitor_tournament WHERE tournament_id = $tournament_id AND user_id = $user_id");
    if ($check && $check->num_rows > 0) {
        $_SESSION['error'] = 'You have already joined this tournament.';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    // Check visitor limit
    $limit = $conn->query("SELECT visitor_limit, COALESCE(visitor_count,0) as vc FROM Tournament WHERE tournament_id = $tournament_id")->fetch_assoc();
    if ($limit && $limit['vc'] >= $limit['visitor_limit']) {
        $_SESSION['error'] = 'Visitor limit reached.';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    // Record payment if required
    if ($entry_fee > 0) {
        $pay_date = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', ?, ?, 'tournament_entry')");
        $stmt->bind_param("ds", $entry_fee, $pay_date);
        $stmt->execute();
        $payment_id = $stmt->insert_id;
        
        // Get tournament's room_id to create a booking record
        $t_info = $conn->query("SELECT room_id FROM Tournament WHERE tournament_id = $tournament_id")->fetch_assoc();
        $room_id = $t_info['room_id'] ?? 0;
        
        // Create a booking record for this tournament entry
        if ($room_id > 0) {
            $conn->query("INSERT INTO Booking (room_id, booking_date, start_time, end_time, status) VALUES ($room_id, '$pay_date', '00:00:00', '23:59:59', 'confirmed')");
            $booking_id = $conn->insert_id;
            
            // Link user, booking, and payment via Makes table
            $conn->query("INSERT INTO Makes (user_id, booking_id, payment_id) VALUES ($user_id, $booking_id, $payment_id)");
        }
    }
    
    // Insert into visitor_tournament table to track this specific user
    $stmt = $conn->prepare("INSERT INTO visitor_tournament (user_id, tournament_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $tournament_id);
    $stmt->execute();
    
    // Increase visitor count
    $conn->query("UPDATE Tournament SET visitor_count = COALESCE(visitor_count, 0) + 1 WHERE tournament_id = $tournament_id");
    
    $_SESSION['success'] = 'Tournament joined successfully!';
    header('Location: dashboard.php?tab=tournaments');
    exit;
}
?>