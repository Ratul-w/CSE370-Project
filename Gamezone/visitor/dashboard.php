<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['visitor', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stats = ['tournaments' => 0, 'games_played' => 0, 'membership_days_left' => 0];

$result = $conn->query("SELECT COUNT(*) as cnt FROM Participate WHERE user_id = $user_id");
if ($result) $stats['tournaments'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM GameSession WHERE user_id = $user_id");
if ($result) $stats['games_played'] = $result->fetch_assoc()['cnt'];

$current = $conn->query("SELECT DATEDIFF(end_date, CURDATE()) as days_left FROM Buy WHERE user_id = $user_id AND end_date >= CURDATE() ORDER BY end_date DESC LIMIT 1");
if ($current && $current->num_rows > 0) {
    $stats['membership_days_left'] = max(0, $current->fetch_assoc()['days_left']);
}

$activeTab = $_GET['tab'] ?? 'dashboard';
$profile = $conn->query("SELECT * FROM User WHERE user_id = $user_id")->fetch_assoc();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $activeTab == 'profile') {
    $stmt = $conn->prepare("UPDATE User SET f_name=?, l_name=?, city=?, postal_code=?, country=? WHERE user_id=?");
    $stmt->bind_param("sssssi", $_POST['f_name'], $_POST['l_name'], $_POST['city'], $_POST['postal_code'], $_POST['country'], $user_id);
    $stmt->execute();
    $profile = $conn->query("SELECT * FROM User WHERE user_id = $user_id")->fetch_assoc();
    $msg = 'Profile updated successfully!';
}
?>
<?php include 'visitor.html'; ?>