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
        $room_name = $_POST['room_name'];
        $room_type = $_POST['room_type'];
        $capacity = $_POST['capacity'];
        
        $stmt = $conn->prepare("INSERT INTO Room (room_name, room_type, capacity, availability_status) VALUES (?, ?, ?, 'available')");
        $stmt->bind_param("ssi", $room_name, $room_type, $capacity);
        $stmt->execute();
    } elseif ($action == 'maintenance') {
        $room_id = $_POST['room_id'];
        $stmt = $conn->prepare("UPDATE Room SET maintenance_status = 'maintenance_needed', availability_status = 'reserved' WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
    } elseif ($action == 'available') {
        $room_id = $_POST['room_id'];
        $stmt = $conn->prepare("UPDATE Room SET maintenance_status = 'good', availability_status = 'available' WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
    } elseif ($action == 'delete') {
        $room_id = $_POST['room_id'];
        $stmt = $conn->prepare("DELETE FROM Room WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
    }
    
    header('Location: dashboard.php?tab=rooms');
    exit;
}
?>