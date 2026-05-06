<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['host', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tournament_id = intval($_POST['tournament_id']);
    $user_id = intval($_POST['user_id']);
    $position = $_POST['position'];
    $prize_amount = floatval($_POST['prize_amount'] ?? 0);
    
    // Update participant status
    $conn->query("UPDATE Participate SET status = '$position' WHERE tournament_id = $tournament_id AND user_id = $user_id");
    
    // Award prize if amount > 0
    if ($prize_amount > 0) {
        // First create prize award record to satisfy foreign key
        $conn->query("INSERT INTO prize_award (position, prize_amount) VALUES ('$position', $prize_amount)");
        $prize_award_id = $conn->insert_id;
        
        // Insert into win table with valid prize_award_id
        $conn->query("INSERT INTO win (user_id, tournament_id, prize_award_id) VALUES ($user_id, $tournament_id, $prize_award_id)");
    }
    
    $_SESSION['success'] = 'Prize awarded successfully!';
    header("Location: dashboard.php?tab=tournaments&view=$tournament_id");
    exit;
}
?>
