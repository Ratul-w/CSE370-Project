<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['action']) && $_POST['action'] == 'cancel') {
        $conn->begin_transaction();
        try {
            $current = $conn->query("SELECT b.*, mp.fee FROM Buy b JOIN MembershipPlan mp ON b.plan_id = mp.plan_id WHERE b.user_id = $user_id AND b.end_date >= CURDATE() LIMIT 1")->fetch_assoc();
            if ($current) {
                $refund_amount = $current['fee'];
                $stmt = $conn->prepare("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES ('completed', ?, CURDATE(), 'refund')");
                $stmt->bind_param("d", $refund_amount);
                $stmt->execute();
                
                $conn->query("UPDATE Buy SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE user_id = $user_id AND end_date >= CURDATE()");
                
                $conn->commit();
                $_SESSION['success'] = 'Membership cancelled! Refund of ' . number_format($refund_amount, 2) . ' BDT processed.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Cancellation failed.';
        }
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    $plan_id = intval($_POST['plan_id'] ?? 0);
    $fee = floatval($_POST['fee'] ?? 0);
    $card_number = intval($_POST['card_number'] ?? 0);
    
    if ($card_number < 10000000000 || $card_number > 99999999999) {
        $_SESSION['error'] = 'Please enter a valid 11-digit credit card number.';
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    if ($plan_id == 0) {
        $_SESSION['error'] = 'Invalid plan selected.';
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    $active_check = $conn->query("SELECT * FROM Buy WHERE user_id = $user_id AND end_date >= CURDATE()");
    if ($active_check && $active_check->num_rows > 0) {
        $_SESSION['error'] = 'You already have an active membership. Please wait until it expires to purchase a new one.';
        header('Location: dashboard.php?tab=membership');
        exit;
    }
    
    $plan = $conn->query("SELECT * FROM MembershipPlan WHERE plan_id = $plan_id")->fetch_assoc();
    if ($plan) {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$plan['duration']} days"));
        
        $conn->begin_transaction();
        try {
            $pay_status = 'completed';
            $pay_type = 'membership';
            $stmt = $conn->prepare("INSERT INTO Payment (pay_status, amount, pay_date, pay_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $pay_status, $fee, $start_date, $pay_type);
            $stmt->execute();
            
            $payment_id = $conn->insert_id;
            
            $stmt = $conn->prepare("INSERT INTO Buy (user_id, payment_id, plan_id, end_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $user_id, $payment_id, $plan_id, $end_date);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = 'Membership purchased successfully!';
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Purchase failed. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'Plan not found.';
    }
    
    header('Location: dashboard.php?tab=membership');
    exit;
}
?>