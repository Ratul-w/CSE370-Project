<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'delete') {
        $booking_id = intval($_POST['booking_id']);
        
        // Delete from Makes first
        $stmt = $conn->prepare("DELETE FROM Makes WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Delete from Booking
        $stmt = $conn->prepare("DELETE FROM Booking WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
    } elseif ($action == 'cancel') {
        $booking_id = intval($_POST['booking_id']);
        
        $stmt = $conn->prepare("UPDATE Booking SET status = 'cancelled' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
    }
    
    header('Location: dashboard.php?tab=bookings');
    exit;
}
?>