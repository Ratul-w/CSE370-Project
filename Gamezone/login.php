<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT user_id, f_name, l_name, email, password, role, status FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] === 'banned') {
            $_SESSION['error'] = 'Your account has been banned. Contact admin for support.';
            header('Location: index.php');
            exit;
        }

        if ($user['status'] === 'suspended') {
            $_SESSION['error'] = 'Your account is currently suspended.';
            header('Location: index.php');
            exit;
        }

        if ($role !== $user['role']) {
            $_SESSION['error'] = 'Invalid role selected for this account.';
            header('Location: index.php');
            exit;
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['f_name'] . ' ' . $user['l_name'];
            $_SESSION['f_name'] = $user['f_name'];
            $_SESSION['l_name'] = $user['l_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['status'] = $user['status'];

            $conn->query("UPDATE User SET visit_count = visit_count + 1 WHERE user_id = {$user['user_id']}");

            switch ($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'host':
                    header('Location: host/dashboard.php');
                    break;
                case 'player':
                    header('Location: player/dashboard.php');
                    break;
                default:
                    header('Location: visitor/dashboard.php');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Invalid password.';
        }
    } else {
        $_SESSION['error'] = 'User not found with this email and role.';
    }

    header('Location: index.php');
    exit;
}
?>