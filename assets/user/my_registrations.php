<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

$sql = "SELECT e.event_id, e.title, e.artist_name, e.date, e.location, r.registered_at
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.user_id = ?
        ORDER BY e.date ASC, r.registered_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$registrations = $result->fetch_all(MYSQLI_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations</title>

    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-title { color: var(--heading-color); }
    </style>
</head>
<body>
    <header class="p-3 mb-3 border-bottom bg-white shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h3 class="m-0" style="color: var(--heading-color);">My Registrations</h3>
                <small class="text-muted">Review the events you've joined</small>
            </div>
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Home</a>
        </div>
    </header>

    <main class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Registered Events</h5>
                    <span class="badge bg-primary-subtle text-primary">Total: <?= count($registrations) ?></span>
                </div>

                <?php if (empty($registrations)): ?>
                    <div class="alert alert-info mb-0">
                        You haven't registered for any events yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Event</th>
                                    <th scope="col">Artist</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Location</th>
                                    <th scope="col">Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($registrations as $reg): ?>
                                    <tr>
                                        <th scope="row"><?= $i++ ?></th>
                                        <td class="fw-semibold"><?= e($reg['title']) ?></td>
                                        <td><?= e($reg['artist_name']) ?></td>
                                        <td><?= e(date('M d, Y', strtotime($reg['date']))) ?></td>
                                        <td><?= e($reg['location']) ?></td>
                                        <td><?= e(date('M d, Y H:i', strtotime($reg['registered_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>