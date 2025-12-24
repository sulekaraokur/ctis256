
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php"; 

$msg = "";
$errors = [];

// Sticky values
$title = $artist_name = $descr = $date = $loc = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelenler
    $title = trim($_POST['title'] ?? '');
    $artist_name = trim($_POST['artist_name'] ?? '');
    $descr = trim($_POST['descr'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $loc = trim($_POST['loc'] ?? '');

    // Validation
    if ($title === '' || $artist_name === '' || $descr === '' || $date === '' || $loc === '') {
        $errors[] = "Please fill in all the required fields.";
    }

    if (!$errors) {
        try {
            $organizer_id = 1;

$sql = "INSERT INTO events
        (organizer_id, title, artist_name, `desc`, date, location, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";

$stmt = $pdo->prepare($sql);
$stmt->execute([$organizer_id, $title, $artist_name, $descr, $date, $loc]);


            $msg = "Event added successfully!";
            $title = $artist_name = $descr = $date = $loc = "";
        } catch (Exception $ex) {
            $errors[] = "DB error: " . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Event</title>
</head>
<body>
<!-- test -->
    <h2>Add New Event</h2>

    <?php if ($msg): ?>
        <p style="color: green;"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
        <p style="color: red;"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form action="" method="post">
        <label for="title">Event Title:</label><br>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required><br><br>

        <label for="artist_name">Artist Name:</label><br>
        <input type="text" id="artist_name" name="artist_name" value="<?= htmlspecialchars($artist_name) ?>" required><br><br>

        <label for="descr">Description:</label><br>
        <textarea id="descr" name="descr" required><?= htmlspecialchars($descr) ?></textarea><br><br>

        <label for="date">Date:</label><br>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date) ?>" required><br><br>

        <label for="loc">Location:</label><br>
        <input type="text" id="loc" name="loc" value="<?= htmlspecialchars($loc) ?>" required><br><br>

        <input type="submit" name="addBtn" value="Add Event">
    </form>
</body>
</html>
