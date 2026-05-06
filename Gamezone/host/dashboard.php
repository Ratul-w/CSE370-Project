<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['host', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

$host_id = $_SESSION['user_id'];
$stats = ['total_bookings' => 0, 'total_revenue' => 0, 'available_rooms' => 0, 'membership_days_left' => 0];

$result = $conn->query("SELECT COUNT(*) as cnt FROM Booking");
if ($result) $stats['total_bookings'] = $result->fetch_assoc()['cnt'];

// Revenue: visitor tournament fees from visitor_tournament table
$host_tournaments = $conn->query("SELECT tournament_id, entry_fee FROM Tournament WHERE user_id = $host_id");
$total = 0;
if ($host_tournaments) {
    while($t = $host_tournaments->fetch_assoc()) {
        $tid = $t['tournament_id'];
        $fee = floatval($t['entry_fee']);
        
        // Active visitors = full fee
        $active = $conn->query("SELECT COUNT(*) as c FROM visitor_tournament WHERE tournament_id = $tid AND user_id > 0")->fetch_assoc()['c'];
        
        // Withdrawn = 60% (40% refunded)
        $withdrawn = $conn->query("SELECT COUNT(*) as c FROM visitor_tournament WHERE tournament_id = $tid AND user_id < 0")->fetch_assoc()['c'];
        
        $total += (intval($active) * $fee) + (intval($withdrawn) * $fee * 0.60);
    }
}
$stats['total_revenue'] = $total;

$result = $conn->query("SELECT COUNT(*) as cnt FROM Room WHERE availability_status = 'available'");
if ($result) $stats['available_rooms'] = $result->fetch_assoc()['cnt'];

$current = $conn->query("SELECT DATEDIFF(end_date, CURDATE()) as days_left FROM Buy WHERE user_id = $host_id AND end_date >= CURDATE() ORDER BY end_date DESC LIMIT 1");
if ($current && $current->num_rows > 0) {
    $stats['membership_days_left'] = max(0, $current->fetch_assoc()['days_left']);
}

$activeTab = $_GET['tab'] ?? 'dashboard';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $activeTab == 'profile') {
    $stmt = $conn->prepare("UPDATE User SET f_name=?, l_name=?, city=?, postal_code=?, country=? WHERE user_id=?");
    $stmt->bind_param("sssssi", $_POST['f_name'], $_POST['l_name'], $_POST['city'], $_POST['postal_code'], $_POST['country'], $host_id);
    $stmt->execute();
    $msg = 'Profile updated successfully!';
}

$profile = $conn->query("SELECT * FROM User WHERE user_id = $host_id")->fetch_assoc();
?>
<?php include 'host.html'; ?>