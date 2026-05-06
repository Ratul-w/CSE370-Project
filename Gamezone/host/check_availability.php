<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$msg = '';
if (isset($_GET['room_id']) && isset($_GET['date'])) {
    $room_id = intval($_GET['room_id']);
    $date = $_GET['date'];
    
    $room = $conn->query("SELECT room_name FROM Room WHERE room_id = $room_id")->fetch_assoc();
    $check = $conn->query("SELECT t_name FROM Tournament WHERE room_id = $room_id AND start_date = '$date' AND status != 'completed'");
    
    if ($check && $check->num_rows > 0) {
        $t = $check->fetch_assoc();
        $msg = '<div class="alert alert-danger">Already booked for "'.$t['t_name'].'"</div>';
    } else {
        $msg = '<div class="alert alert-success">Room Available!</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Availability</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h5>Check Room Availability</h5>
    <?= $msg ?>
    <form>
        <div class="mb-3">
            <label class="form-label">Select Room</label>
            <select name="room_id" class="form-select" required>
                <option value="">Select Room</option>
                <?php
                $rooms = $conn->query("SELECT * FROM Room WHERE availability_status = 'available' AND room_type = 'tournament'");
                while ($room = $rooms->fetch_assoc()):
                ?>
                <option value="<?= $room['room_id'] ?>"><?= htmlspecialchars($room['room_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Check</button>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</body>
</html>