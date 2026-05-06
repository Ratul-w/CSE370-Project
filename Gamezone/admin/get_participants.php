<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<p class="text-danger">Access denied</p>';
    exit;
}

$tournament_id = intval($_GET['tournament_id'] ?? 0);

if ($tournament_id <= 0) {
    echo '<p class="text-danger">Invalid tournament</p>';
    exit;
}

$participants = $conn->query("
    SELECT p.*, u.f_name, u.l_name, u.email, u.role 
    FROM Participate p 
    LEFT JOIN User u ON p.user_id = u.user_id 
    WHERE p.tournament_id = $tournament_id
    ORDER BY p.registration_date DESC
");

if ($participants && $participants->num_rows > 0):
?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Registered</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($p = $participants->fetch_assoc()): 
        $userName = trim(($p['f_name'] ?? '') . ' ' . ($p['l_name'] ?? 'Unknown'));
    ?>
        <tr>
            <td><?php echo htmlspecialchars($userName); ?></td>
            <td><?php echo htmlspecialchars($p['email'] ?? 'N/A'); ?></td>
            <td><span class="badge bg-primary"><?php echo $p['role'] ?? 'Unknown'; ?></span></td>
            <td><span class="badge bg-<?php echo $p['status'] == 'registered' ? 'success' : 'warning'; ?>"><?php echo $p['status']; ?></span></td>
            <td><?php echo $p['registration_date'] ?? 'N/A'; ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p class="text-muted">No participants registered yet.</p>
<?php endif;
?>