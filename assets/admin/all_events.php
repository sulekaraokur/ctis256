<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: /ctis256_proje/index.php");
    exit;
}

// Filters (view only)
$status    = $_GET['status'] ?? 'all';
$allowed   = ['pending', 'approved', 'rejected'];
$hasFilter = in_array($status, $allowed, true);

$sql = "
    SELECT 
        e.event_id,
        e.title,
        e.date,
        e.status,
        u.username AS organizer
    FROM events e
    LEFT JOIN users u ON e.organizer_id = u.user_id
";

if ($hasFilter) {
    $sql .= " WHERE e.status = ? ";
}

$sql .= " ORDER BY e.date DESC";

$stmt = $conn->prepare($sql);
if ($hasFilter) {
    $stmt->bind_param("s", $status);
}
$stmt->execute();
$result = $stmt->get_result();
$events = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function statusBadge(string $status): string
{
    if ($status === 'approved') {
        return '<span class="badge bg-success-subtle text-success">Approved</span>';
    }
    if ($status === 'pending') {
        return '<span class="badge bg-warning-subtle text-dark">Pending</span>';
    }
    return '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events</title>
    <link href="/ctis256_proje/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
</head>
<body class="bg-light">
<section class="section">
    <div class="container">
        <div class="section-title d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="mb-1">All Events</h2>
                <p class="text-muted mb-0">View event information and approval status.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary">&larr; Back to Dashboard</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Events</h5>
                    <span class="badge bg-primary-subtle text-primary">Total: <?= count($events) ?></span>
                </div>

                <div class="mb-3">
                    <a href="all_events.php" class="btn btn-sm <?= $status === 'all' ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
                    <a href="all_events.php?status=pending" class="btn btn-sm <?= $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
                    <a href="all_events.php?status=approved" class="btn btn-sm <?= $status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
                    <a href="all_events.php?status=rejected" class="btn btn-sm <?= $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
                </div>

                <?php if (empty($events)): ?>
                    <p class="mb-0">No events found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th scope="col" style="width: 80px;">ID</th>
                                    <th scope="col">Title</th>
                                    <th scope="col" style="width: 160px;">Date</th>
                                    <th scope="col" style="width: 200px;">Organizer</th>
                                    <th scope="col" style="width: 140px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $index => $row): ?>
                                    <tr class="text-center">
                                        <th scope="row">#<?= (int) $row['event_id'] ?></th>
                                        <td class="text-start"><?= e($row['title']) ?></td>
                                        <td><?= e($row['date']) ?></td>
                                        <td><?= e($row['organizer'] ?? 'Unknown') ?></td>
                                        <td><?= statusBadge($row['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
</body>
</html>