<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$stats = ['total_users' => 0, 'active_members' => 0, 'banned_users' => 0, 'total_revenue' => 0, 'total_refunds' => 0, 'net_revenue' => 0, 'available_rooms' => 0, 'active_tournaments' => 0, 'total_payments' => 0, 'total_refund_count' => 0];

$result = $conn->query("SELECT COUNT(*) as cnt FROM User");
if ($result) $stats['total_users'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM User WHERE status = 'active'");
if ($result) $stats['active_members'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM User WHERE status IN ('banned', 'suspended')");
if ($result) $stats['banned_users'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM Payment WHERE pay_status = 'completed' AND pay_type = 'membership'");
if ($result) $stats['total_revenue'] = $result->fetch_assoc()['total'];

// Get total tournament_entry payments
$t_total = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM Payment WHERE pay_status = 'completed' AND pay_type = 'tournament_entry'");
$total_tournament = $t_total ? $t_total->fetch_assoc()['total'] : 0;

// Get visitor payments via Makes
$v_total = $conn->query("
    SELECT COALESCE(SUM(p.amount), 0) as total 
    FROM Payment p 
    JOIN Makes m ON p.payment_id = m.payment_id 
    JOIN User u ON m.user_id = u.user_id 
    WHERE p.pay_status = 'completed' 
    AND p.pay_type = 'tournament_entry'
    AND u.role = 'visitor'
");
$visitor_tournament = $v_total ? $v_total->fetch_assoc()['total'] : 0;

// Admin gets: all minus visitor (visitor goes to host)
$stats['total_revenue'] += ($total_tournament - $visitor_tournament);

$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM Payment WHERE pay_status = 'completed' AND pay_type = 'refund'");
if ($result) $stats['total_refunds'] = $result->fetch_assoc()['total'];

$stats['net_revenue'] = $stats['total_revenue'] - $stats['total_refunds'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM Room WHERE availability_status = 'available'");
if ($result) $stats['available_rooms'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM Tournament WHERE status != 'completed'");
if ($result) $stats['active_tournaments'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM Payment WHERE pay_status = 'completed'");
if ($result) $stats['total_payments'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM Payment WHERE pay_type = 'refund' AND pay_status = 'completed'");
if ($result) $stats['total_refund_count'] = $result->fetch_assoc()['cnt'];

$activeTab = $_GET['tab'] ?? 'dashboard';
?>
<?php include 'admin.html'; ?>