<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
$organizer_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$checkSql = "SELECT title FROM events WHERE event_id = ? AND organizer_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) die("Event not found or access denied.");

$sql = "SELECT u.username AS display_name, u.email, r.registered_at
        FROM registrations r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.event_id = ?
        ORDER BY r.registered_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$attendees = [];
while($row = $result->fetch_assoc()) {
    $attendees[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Attendees</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
</head>
<body>
    <main class="main">
        <section class="section">
            <div class="container">
                <div class="section-title">
                    <h2>Attendees</h2>
                    <p>Event: <span style="color:var(--accent-color)"><?= htmlspecialchars($event['title']) ?></span></p>
                </div>
                <div class="mb-3"><a href="my_events.php" class="btn btn-secondary">&larr; Back</a></div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Registered Users (<?= count($attendees) ?>)</h5>
                        <table class="table table-hover mt-3">
                            <thead><tr><th>#</th><th>Display Name</th><th>Email</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php $i=1; foreach($attendees as $p): ?>
                                <tr><td><?= $i++ ?></td><td><?= htmlspecialchars($p['display_name']) ?></td><td><?= htmlspecialchars($p['email']) ?></td><td><?= $p['registered_at'] ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>