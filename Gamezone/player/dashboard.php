<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stats = ['membership' => 'None', 'tournaments' => 0, 'sessions' => 0, 'membership_days_left' => 0, 'prize_money' => 0];

$m = $conn->query("SELECT mp.plan_name, DATEDIFF(b.end_date, CURDATE()) as days_left FROM Buy b JOIN MembershipPlan mp ON b.plan_id = mp.plan_id WHERE b.user_id = $user_id AND b.end_date >= CURDATE() LIMIT 1");
if ($m && $m->num_rows > 0) {
    $row = $m->fetch_assoc();
    $stats['membership'] = $row['plan_name'];
    $stats['membership_days_left'] = max(0, $row['days_left']);
}

$r = $conn->query("SELECT COUNT(*) as c FROM Participate WHERE user_id = $user_id");
if ($r) $stats['tournaments'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM GameSession WHERE user_id = $user_id");
if ($r) $stats['sessions'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COALESCE(SUM(pa.prize_amount), 0) as total FROM win w JOIN prize_award pa ON w.prize_award_id = pa.prize_award_id WHERE w.user_id = $user_id");
if ($r) $stats['prize_money'] = $r->fetch_assoc()['total'];

$activeTab = $_GET['tab'] ?? 'dashboard';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE User SET f_name=?,l_name=?,city=?,postal_code=?,country=?,gamertag=? WHERE user_id=?");
    $stmt->bind_param("ssssssi", $_POST['f_name'], $_POST['l_name'], $_POST['city'], $_POST['postal_code'], $_POST['country'], $_POST['gamertag'], $user_id);
    $stmt->execute();
    $msg = 'Updated!';
}

$profile = $conn->query("SELECT * FROM User WHERE user_id = $user_id")->fetch_assoc();
?>
<?php include 'player.html'; ?>