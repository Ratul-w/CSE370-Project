<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $f_name = trim($_POST['f_name']);
    $l_name = trim($_POST['l_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $gamertag = trim($_POST['gamertag'] ?? '');

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: index.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters.';
        header('Location: index.php');
        exit;
    }

    $check_stmt = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = 'Email already exists.';
        header('Location: index.php');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'player' && !empty($gamertag)) {
        $sql = "INSERT INTO User (f_name, l_name, email, password, role, gamertag) VALUES ('$f_name', '$l_name', '$email', '$hashed_password', '$role', '$gamertag')";
    } else {
        $sql = "INSERT INTO User (f_name, l_name, email, password, role) VALUES ('$f_name', '$l_name', '$email', '$hashed_password', '$role')";
    }
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Registration successful! Please sign in.';
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
    }

    header('Location: index.php');
    exit;
}
?>