<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $plan_name = $_POST['plan_name'];
        $description = $_POST['description'] ?? '';
        $fee = floatval($_POST['fee']);
        $duration = intval($_POST['duration']);
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("INSERT INTO MembershipPlan (plan_name, description, duration, fee, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssids", $plan_name, $description, $duration, $fee, $role);
        $stmt->execute();
    } elseif ($action == 'delete') {
        $plan_id = intval($_POST['plan_id']);
        $stmt = $conn->prepare("DELETE FROM MembershipPlan WHERE plan_id = ?");
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
    }
    
    header('Location: dashboard.php?tab=membership');
    exit;
}
?>