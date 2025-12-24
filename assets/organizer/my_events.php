<?php
session_start();
// Hata raporlamayı açalım
ini_set('display_errors', 1); error_reporting(E_ALL);

// DB Yolu
require_once "../includes/db.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$organizer_id = $_SESSION['user_id'];

// Filtreleme
$status = $_GET['status'] ?? 'all';
$allowed_status = ['pending', 'approved', 'rejected'];

// Etkinlikleri Çek
$sql = "SELECT * FROM events WHERE organizer_id = ?";
if (in_array($status, $allowed_status)) {
    $sql .= " AND status = ?";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($status !== 'all' && in_array($status, $allowed_status)) {
    $stmt->bind_param("is", $organizer_id, $status);
} else {
    $stmt->bind_param("i", $organizer_id);
}
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

// İstatistikler
$cStmt = $conn->prepare("SELECT status, COUNT(*) as c FROM events WHERE organizer_id = ? GROUP BY status");
$cStmt->bind_param("i", $organizer_id);
$cStmt->execute();
$cResult = $cStmt->get_result();
$stats = ['pending'=>0, 'approved'=>0, 'rejected'=>0];
while($row = $cResult->fetch_assoc()) {
    $stats[$row['status']] = $row['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>

    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .card-header { background-color: #fff; border-bottom: 1px solid #eee; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: 600; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; }
        .status-pending { background-color: #fff3cd; color: #664d03; }
        .status-rejected { background-color: #f8d7da; color: #842029; }
        .img-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
    </style>
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
                <h2 class="fw-bold">My Events</h2>
                <p class="text-muted">Manage your events and track attendees</p>
            </div>
            <a href="add_event.php" class="btn btn-primary btn-lg shadow-sm">
                <i class="bi bi-plus-lg"></i> Add New Event
            </a>
        </div>

        <!-- Filtre Butonları -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body d-flex gap-2">
                <a href="my_events.php?status=all" class="btn btn-sm <?= $status=='all'?'btn-dark':'btn-outline-dark' ?>">
                    All Events
                </a>
                <a href="my_events.php?status=approved" class="btn btn-sm <?= $status=='approved'?'btn-success':'btn-outline-success' ?>">
                    Approved <span class="badge bg-white text-success ms-1"><?= $stats['approved'] ?></span>
                </a>
                <a href="my_events.php?status=pending" class="btn btn-sm <?= $status=='pending'?'btn-warning':'btn-outline-warning' ?>">
                    Pending <span class="badge bg-white text-warning ms-1"><?= $stats['pending'] ?></span>
                </a>
                <a href="my_events.php?status=rejected" class="btn btn-sm <?= $status=='rejected'?'btn-danger':'btn-outline-danger' ?>">
                    Rejected <span class="badge bg-white text-danger ms-1"><?= $stats['rejected'] ?></span>
                </a>
            </div>
        </div>

        <!-- Mesajlar -->
        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Event deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Event updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tablo -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Image</th>
                                <th>Event Details</th>
                                <th>Date & Location</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($events)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                                        No events found in this category.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($events as $e): ?>
                                <tr>
                                    <!-- RESİM KISMI DÜZELTİLDİ: image_path YERİNE image YAZILDI -->
                                    <td class="ps-4">
                                        <?php if(!empty($e['image'])): ?>
                                            <img src="../../uploads/<?= htmlspecialchars($e['image']) ?>" class="img-thumb shadow-sm">
                                        <?php else: ?>
                                            <div class="img-thumb bg-secondary text-white d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($e['title']) ?></h6>
                                        <small class="text-muted"><i class="bi bi-mic"></i> <?= htmlspecialchars($e['artist_name']) ?></small>
                                    </td>
                                    <td>
                                        <div class="small text-dark"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($e['date']) ?></div>
                                        <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['location']) ?></div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $e['status'] ?>">
                                            <?= strtoupper($e['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="attendees.php?id=<?= $e['event_id'] ?>" class="btn btn-sm btn-info text-white" title="Attendees">
                                                <i class="bi bi-people-fill"></i> <span class="d-none d-md-inline">Attendees</span>
                                            </a>
                                            <a href="edit_event.php?id=<?= $e['event_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="delete_event.php?id=<?= $e['event_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this event?')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
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