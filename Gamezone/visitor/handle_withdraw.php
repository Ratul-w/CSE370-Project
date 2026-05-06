<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['visitor', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $tournament_id = $_POST['tournament_id'];
    
    // Delete from visitor_tournament table
    $conn->query("DELETE FROM visitor_tournament WHERE tournament_id = $tournament_id AND user_id = $user_id");
    
    // Decrease visitor count
    $conn->query("UPDATE Tournament SET visitor_count = COALESCE(visitor_count, 0) - 1 WHERE tournament_id = $tournament_id AND visitor_count > 0");
    
    $_SESSION['success'] = 'Withdrawn full refund given.';
    header('Location: dashboard.php?tab=tournaments');
    exit;
}
?>