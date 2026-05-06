<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $game_id = intval($_POST['game_id']);
    $action = $_POST['action'];
    
    if ($action == 'start') {
        $conn->query("INSERT INTO player_sessions (user_id, game_id) VALUES ($user_id, $game_id)");
        header('Location: dashboard.php?tab=games');
        exit;
    } elseif ($action == 'end') {
        $session_id = intval($_POST['session_id']);
        $duration = intval($_POST['duration']);
        $conn->query("UPDATE player_sessions SET exit_time = NOW(), duration_minutes = $duration WHERE id = $session_id");
        header('Location: dashboard.php?tab=history');
        exit;
    }
}
?>