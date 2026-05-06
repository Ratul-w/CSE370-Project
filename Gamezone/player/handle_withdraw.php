<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $tournament_id = intval($_POST['tournament_id'] ?? 0);
    $entry_fee = floatval($_POST['entry_fee'] ?? 0);
    
    if ($tournament_id == 0) {
        $_SESSION['error'] = 'Invalid tournament';
        header('Location: dashboard.php?tab=tournaments');
        exit;
    }
    
    // Delete the participation
    $conn->query("DELETE FROM Participate WHERE tournament_id = $tournament_id AND user_id = $user_id");
    
    // Refund 40% if there was entry fee
    if ($entry_fee > 0) {
        $refund_amount = $entry_fee * 0.40;
        $conn->query("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', $refund_amount, CURDATE(), 'refund')");
        $_SESSION['success'] = 'Withdrawn! 40% refund (' . number_format($refund_amount, 0) . ' BDT) credited to your account.';
    } else {
        $_SESSION['success'] = 'Withdrawn from tournament!';
    }
    
    header('Location: dashboard.php?tab=tournaments');
    exit;
}
?>