<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    
    $req = $conn->query("SELECT * FROM refund_request WHERE request_id = $request_id")->fetch_assoc();
    
    if ($action == 'approve') {
        $conn->query("UPDATE refund_request SET status = 'approved' WHERE request_id = $request_id");
        
        $pay_date = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', ?, ?, 'refund')");
        $stmt->bind_param("ds", $req['amount'], $pay_date);
        $stmt->execute();
    } elseif ($action == 'reject') {
        $conn->query("UPDATE refund_request SET status = 'rejected' WHERE request_id = $request_id");
    }
    
    header('Location: dashboard.php?tab=refunds');
    exit;
}
?>