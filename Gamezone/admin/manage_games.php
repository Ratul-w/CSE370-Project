<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$all_games = [];
$admin_query = $conn->query("SELECT available_games FROM admin LIMIT 1");
if ($admin_query) {
    $admin = $admin_query->fetch_assoc();
    if ($admin && !empty($admin['available_games'])) {
        $all_games = array_map('trim', explode(',', $admin['available_games']));
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_game'])) {
    $new_game = trim($_POST['new_game']);
    if (!empty($new_game) && !in_array($new_game, $all_games)) {
        $all_games[] = $new_game;
        $games_str = implode(', ', $all_games);
        $stmt = $conn->prepare("UPDATE admin SET available_games = ? WHERE admin_id = 1");
        if ($stmt) {
            $stmt->bind_param("s", $games_str);
            $stmt->execute();
        }
        $_SESSION['success'] = "Game '$new_game' added!";
    }
    header('Location: manage_games.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_game'])) {
    $game_to_delete = trim($_POST['delete_game']);
    $all_games = array_filter($all_games, function($g) use ($game_to_delete) {
        return $g !== $game_to_delete;
    });
    $games_str = implode(', ', $all_games);
    $stmt = $conn->prepare("UPDATE admin SET available_games = ? WHERE admin_id = 1");
    if ($stmt) {
        $stmt->bind_param("s", $games_str);
        $stmt->execute();
    }
    $_SESSION['success'] = "Game '$game_to_delete' deleted!";
    header('Location: manage_games.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_game'])) {
    $room_id = intval($_POST['room_id']);
    $selected_games = $_POST['games'] ?? [];
    $games_str = implode(', ', $selected_games);
    
    $stmt = $conn->prepare("UPDATE Room SET available_games = ? WHERE room_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $games_str, $room_id);
        $stmt->execute();
    }
    $_SESSION['success'] = 'Games updated for room!';
    header('Location: manage_games.php');
    exit;
}

$rooms = $conn->query("SELECT * FROM Room WHERE room_type = 'regular' ORDER BY room_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games - GameZone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
    <style>
        .sidebar-admin { position: fixed; width: 250px; height: 100vh; background: linear-gradient(180deg, #1a1a2e, #16213e); color: white; padding: 20px 0; z-index: 1000; }
        .sidebar-admin .logo { font-size: 24px; font-weight: bold; text-align: center; padding: 20px; background: rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-admin nav a { display: block; padding: 15px 25px; color: #aaa; text-decoration: none; transition: all 0.3s; }
        .sidebar-admin nav a:hover, .sidebar-admin nav a.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid #667eea; }
        .main-content { margin-left: 250px; padding: 30px; padding-top: 70px; min-height: 100vh; }
        @media (max-width: 768px) { .sidebar-admin { position: static; width: 100%; height: auto; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar-admin">
        <div class="logo">GameZone Admin</div>
        <nav class="nav flex-column">
            <a href="dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a>
            <a href="manage_games.php" class="active"><i class="bi bi-controller me-2"></i>Manage Games</a>
            <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <h4 class="mb-4">Manage Games for Rooms</h4>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card p-4 mb-4">
            <h5 class="mb-3">Add New Game to Global List</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="new_game" class="form-control" placeholder="Enter game name (e.g., Valorant, PUBG, FIFA 26)" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_game" class="btn btn-primary w-100">Add Game</button>
                </div>
            </form>
            <?php if (!empty($all_games)): ?>
            <div class="mt-3">
                <small class="text-muted">Current games:</small>
                <div class="mt-2">
                    <?php foreach ($all_games as $game): ?>
                    <span class="badge bg-primary me-2 mb-1 d-inline-flex align-items-center">
                        <?php echo htmlspecialchars($game); ?>
                        <form method="POST" class="d-inline ms-2" onsubmit="return confirm('Delete this game?')">
                            <input type="hidden" name="delete_game" value="<?php echo htmlspecialchars($game); ?>">
                            <button type="submit" name="delete_game_btn" class="btn btn-sm btn-light text-danger p-0 py-1 px-1" style="border:none;background:transparent;">&times;</button>
                        </form>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <h5 class="mb-3">Assign Games to Regular Rooms</h5>
        <div class="row g-4">
            <?php 
            if ($rooms && $rooms->num_rows > 0):
                while ($room = $rooms->fetch_assoc()): 
                    $room_games = !empty($room['available_games']) ? array_map('trim', explode(',', $room['available_games'])) : [];
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card p-3">
                    <h6><?php echo htmlspecialchars($room['room_name']); ?></h6>
                    <p class="text-muted small mb-2">Capacity: <?php echo $room['capacity']; ?> people</p>
                    <form method="POST">
                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                        <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($all_games)): ?>
                                <?php foreach ($all_games as $game): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="games[]" value="<?php echo htmlspecialchars($game); ?>" 
                                           id="room<?php echo $room['room_id']; ?>_<?php echo md5($game); ?>" 
                                           <?php echo in_array($game, $room_games) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="room<?php echo $room['room_id']; ?>_<?php echo md5($game); ?>">
                                        <?php echo htmlspecialchars($game); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No games added yet.</p>
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="assign_game" class="btn btn-success btn-sm w-100">Save Games</button>
                    </form>
                </div>
            </div>
            <?php 
                endwhile; 
            endif; 
            ?>
        </div>

        <a href="dashboard.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>