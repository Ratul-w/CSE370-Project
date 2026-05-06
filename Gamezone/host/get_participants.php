<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['host', 'admin'])) {
    echo '<p class="text-danger">Access denied</p>';
    exit;
}

$tournament_id = intval($_GET['tournament_id'] ?? 0);

$participants = $conn->query("
    SELECT p.*, u.f_name, u.l_name, u.email, u.role 
    FROM Participate p 
    LEFT JOIN User u ON p.user_id = u.user_id 
    WHERE p.tournament_id = $tournament_id
    ORDER BY p.registration_date DESC
");

if (!$participants) {
    echo '<p class="text-danger">Query failed</p>';
    exit;
}

if ($participants->num_rows == 0) {
    echo '<p class="text-muted">No participants found for this tournament.</p>';
    exit;
}

while ($p = $participants->fetch_assoc()) {
    $name = $p['f_name'] . ' ' . $p['l_name'];
    if (trim($name) == '') $name = 'Unknown';
    
    echo '<div class="row mb-2">';
    echo '<div class="col-md-3">' . htmlspecialchars($name) . '</div>';
    echo '<div class="col-md-3">' . htmlspecialchars($p['email']) . '</div>';
    echo '<div class="col-md-2"><span class="badge bg-primary">' . $p['role'] . '</span></div>';
    echo '<div class="col-md-2"><span class="badge bg-success">' . $p['status'] . '</span></div>';
    echo '<div class="col-md-2">' . $p['registration_date'] . '</div>';
    echo '</div>';
}
?>