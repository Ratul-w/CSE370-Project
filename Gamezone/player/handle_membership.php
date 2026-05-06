<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['player', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['action']) && $_POST['action'] == 'cancel') {
        $current = $conn->query("SELECT b.*, mp.fee FROM Buy b JOIN MembershipPlan mp ON b.plan_id = mp.plan_id WHERE b.user_id = $user_id AND b.end_date >= CURDATE() LIMIT 1")->fetch_assoc();
        if ($current) {
            $refund_amount = $current['fee'] * 0.40;
            $conn->query("UPDATE Buy SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE user_id = $user_id AND end_date >= CURDATE()");
            if ($refund_amount > 0) {
                $conn->query("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', $refund_amount, CURDATE(), 'refund')");
                $_SESSION['success'] = 'Membership cancelled! 40% refund (' . number_format($refund_amount, 0) . ' BDT) credited to your account.';
            } else {
                $_SESSION['success'] = 'Membership cancelled!';
            }
        }
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    $plan_id = intval($_POST['plan_id'] ?? 0);
    $fee = floatval($_POST['fee'] ?? 0);
    $card_number = $_POST['card_number'] ?? '';
    
    if (strlen($card_number) != 11) {
        $_SESSION['error'] = 'Enter 11-digit card number';
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    $check = $conn->query("SELECT * FROM Buy WHERE user_id = $user_id AND end_date >= CURDATE()");
    if ($check && $check->num_rows > 0) {
        $_SESSION['error'] = 'Already has active membership';
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    $plan = $conn->query("SELECT * FROM MembershipPlan WHERE plan_id = $plan_id AND role = 'player'")->fetch_assoc();
    if ($plan) {
        $end_date = date('Y-m-d', strtotime("+{$plan['duration']} days"));
        $conn->query("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', $fee, CURDATE(), 'membership')");
        $payment_id = $conn->insert_id;
        $conn->query("INSERT INTO Buy (user_id, payment_id, plan_id, end_date) VALUES ($user_id, $payment_id, $plan_id, '$end_date')");
        $_SESSION['success'] = 'Membership purchased!';
    }
    
    header('Location: dashboard.php?tab=membership');
    exit;
}
?>