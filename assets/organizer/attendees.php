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


<!-- UI kısmı düzeltildi. -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendees</title>

    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
</head>
<body>
    <header class="p-3 mb-3 border-bottom bg-white shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <h3 class="m-0" style="color: var(--heading-color);">Organizer Panel</h3>
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm">Go to Home</a>
        </div>
    </header>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted mb-1 small">Event</p>
                <h2 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i>Attendees</h2>
                <p class="text-muted mb-0"><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($event['title']) ?></p>
            </div>
            <a href="my_events.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Events
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Registered Users</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary">Total: <?= count($attendees) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th><i class="bi bi-person-badge me-1"></i> Display Name</th>
                                <th><i class="bi bi-envelope me-1"></i> Email</th>
                                <th><i class="bi bi-clock-history me-1"></i> Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($attendees)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-people display-5 d-block mb-2"></i>
                                        No attendees have registered for this event yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach($attendees as $p): ?>
                                    <?php $formattedDate = date("M d, Y h:i A", strtotime($p['registered_at'])); ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold text-secondary">#<?= $i++ ?></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($p['display_name']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($p['email']) ?></td>
                                        <td class="text-muted"><?= $formattedDate ?></td>
                                    </tr>
                                <?php endforeach; ?>

                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
         </div>
     </main>

    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>