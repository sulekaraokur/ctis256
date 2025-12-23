<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";


$organizer_id = 1;


$status = $_GET['status'] ?? 'all';
$allowed = ['all','pending','approved','rejected'];
if (!in_array($status, $allowed, true)) {
    $status = 'all';
}

$countSql = "
    SELECT LOWER(TRIM(status)) AS s, COUNT(*) AS c
    FROM events
    WHERE organizer_id = ?
    GROUP BY LOWER(TRIM(status))
";
$cs = $pdo->prepare($countSql);
$cs->execute([$organizer_id]);

$counts = ['pending'=>0,'approved'=>0,'rejected'=>0];
foreach ($cs->fetchAll(PDO::FETCH_ASSOC) as $row) {
    if (isset($counts[$row['s']])) {
        $counts[$row['s']] = (int)$row['c'];
    }
}

$totalSql = "SELECT COUNT(*) FROM events WHERE organizer_id = ?";
$ts = $pdo->prepare($totalSql);
$ts->execute([$organizer_id]);
$totalCount = (int)$ts->fetchColumn();

if ($status === 'all') {
    $sql = "SELECT * FROM events
            WHERE organizer_id = ?
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizer_id]);
} else {
    $sql = "SELECT * FROM events
            WHERE organizer_id = ?
              AND LOWER(TRIM(status)) = ?
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizer_id, $status]);
}

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = "";
if (isset($_GET['updated'])) $msg = "Event updated successfully.";
if (isset($_GET['deleted'])) $msg = "Event deleted successfully.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events</title>
</head>
<body>

<h2>My Events</h2>

<?php if ($msg): ?>
    <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<p>
    <a href="add_event.php">+ Add New Event</a> |
    <a href="my_events.php?status=all">All (<?= $totalCount ?>)</a> |
    <a href="my_events.php?status=pending">Pending (<?= $counts['pending'] ?>)</a> |
    <a href="my_events.php?status=approved">Approved (<?= $counts['approved'] ?>)</a> |
    <a href="my_events.php?status=rejected">Rejected (<?= $counts['rejected'] ?>)</a>
</p>

<?php if (!$events): ?>
    <p>
        No events found for filter:
        <b><?= htmlspecialchars($status) ?></b>.
        <a href="my_events.php?status=all">Show all</a>
    </p>
<?php else: ?>
<table border="1" cellpadding="8">
    <tr>
        <th>Title</th>
        <th>Artist</th>
        <th>Date</th>
        <th>Location</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($events as $e): ?>
    <tr>
        <td><?= htmlspecialchars($e['title']) ?></td>
        <td><?= htmlspecialchars($e['artist_name']) ?></td>
        <td><?= htmlspecialchars($e['date']) ?></td>
        <td><?= htmlspecialchars($e['location']) ?></td>
        <td><?= htmlspecialchars($e['status']) ?></td>
        <td>
            <a href="edit_event.php?id=<?= (int)$e['event_id'] ?>">Edit</a> |
            <a href="delete_event.php?id=<?= (int)$e['event_id'] ?>"
               onclick="return confirm('Delete this event?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
