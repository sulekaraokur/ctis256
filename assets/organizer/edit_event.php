<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";

$organizer_id = 1;

$errors = [];
$msg = "";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid event id.");
}

$sql = "SELECT * FROM events WHERE event_id = ? AND organizer_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id, $organizer_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found or you don't have permission.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $artist_name = trim($_POST['artist_name'] ?? '');
    $descr = trim($_POST['descr'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $loc = trim($_POST['loc'] ?? '');

    if ($title === '' || $artist_name === '' || $descr === '' || $date === '' || $loc === '') {
        $errors[] = "Please fill in all the required fields.";
    }

    if (!$errors) {
        try {
            $updateSql = "UPDATE events
                         SET title = ?, artist_name = ?, `desc` = ?, date = ?, location = ?, updated_at = NOW()
                         WHERE event_id = ? AND organizer_id = ?";
            $up = $pdo->prepare($updateSql);
            $up->execute([$title, $artist_name, $descr, $date, $loc, $id, $organizer_id]);

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $organizer_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            header("Location: my_events.php?updated=1");
            exit;

        } catch (Exception $ex) {
            $errors[] = "DB error: " . $ex->getMessage();
        }
    } else {
        $event['title'] = $title;
        $event['artist_name'] = $artist_name;
        $event['desc'] = $descr;
        $event['date'] = $date;
        $event['location'] = $loc;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Event</title>
</head>
<body>
    <h2>Edit Event</h2>

    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form action="" method="post">
        <label>Event Title:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($event['title'] ?? '') ?>" required><br><br>

        <label>Artist Name:</label><br>
        <input type="text" name="artist_name" value="<?= htmlspecialchars($event['artist_name'] ?? '') ?>" required><br><br>

        <label>Description:</label><br>
        <textarea name="descr" required><?= htmlspecialchars($event['desc'] ?? '') ?></textarea><br><br>

        <label>Date:</label><br>
        <input type="date" name="date" value="<?= htmlspecialchars($event['date'] ?? '') ?>" required><br><br>

        <label>Location:</label><br>
        <input type="text" name="loc" value="<?= htmlspecialchars($event['location'] ?? '') ?>" required><br><br>

        <button type="submit">Update</button>
        <a href="my_events.php">Cancel</a>
    </form>
</body>
</html>
