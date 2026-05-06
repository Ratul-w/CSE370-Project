<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_GET['user_id'] ?? 0;
$action = $_GET['action'] ?? '';
$return_tab = $_GET['return_tab'] ?? 'ban';

if ($action === 'unban' && $user_id > 0) {
    $checkUser = $conn->query("SELECT role FROM User WHERE user_id = $user_id")->fetch_assoc();
    if ($checkUser && $checkUser['role'] === 'admin') {
        $_SESSION['error'] = 'Cannot unban an admin.';
        header("Location: dashboard.php?tab=users");
        exit;
    }
    $conn->query("UPDATE User SET status = 'active' WHERE user_id = $user_id");
    header("Location: dashboard.php?tab=$return_tab");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $ban_type = $_POST['ban_type'];
    $ban_period = $_POST['ban_period'];
    $reason = $_POST['reason'];
    $start_date = date('Y-m-d');
    $admin_id = 1;
    
    $checkUser = $conn->query("SELECT role FROM User WHERE user_id = $user_id")->fetch_assoc();
    if ($checkUser && $checkUser['role'] === 'admin') {
        $_SESSION['error'] = 'Cannot ban an admin.';
        header('Location: dashboard.php?tab=users');
        exit;
    }
    
    $end_date = null;
    if ($ban_period === '7days') {
        $end_date = date('Y-m-d', strtotime('+7 days'));
    } elseif ($ban_period === '30days') {
        $end_date = date('Y-m-d', strtotime('+30 days'));
    } elseif ($ban_period === '1year') {
        $end_date = date('Y-m-d', strtotime('+1 year'));
    } elseif ($ban_period === 'permanent') {
        $end_date = null;
    }
    
    $status = ($ban_type === 'banned') ? 'banned' : 'suspended';
    if ($end_date === null) {
        $status = 'banned';
    }
    
    $stmt = $conn->prepare("UPDATE User SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO BanRecord (ban_type, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ban_type, $start_date, $end_date, $reason);
    $stmt->execute();
    
    $ban_id = $conn->insert_id;
    
    $stmt = $conn->prepare("INSERT INTO Implement (user_id, admin_id, ban_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $admin_id, $ban_id);
    $stmt->execute();
    
    header('Location: dashboard.php?tab=ban');
    exit;
}

header('Location: dashboard.php');
exit;