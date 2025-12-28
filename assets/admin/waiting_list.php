<?php
//pending içindeki hata düzeltildi.
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

/* FILTER */
$status = $_GET["status"] ?? "pending";
$allowed = ["pending", "approved", "rejected"];
if (!in_array($status, $allowed)) {
    $status = "pending";
}
//Organizer zaten kayıt sırasında belirleniyor
//Admin’in burada yönettiği şey event onayı, kullanıcı rolü değil
//Bu yüzden sorguda event status’una göre filtreleme yapıyoruz
$stmt = $conn->prepare("
    SELECT 
        e.event_id,
        e.title,
        e.date,
        e.status,
        u.username,
        u.email
    FROM events e
    JOIN users u ON e.organizer_id = u.user_id
    WHERE e.status = ?
    ORDER BY e.date DESC
");

$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Organizer Requests</title>
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <h2 class="mb-4">Organizer Requests</h2>

  <!-- FILTER BUTTONS -->
  <div class="mb-4">
    <a href="?status=pending"
   class="btn <?= $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
  Pending
</a>

<a href="?status=approved"
   class="btn <?= $status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
  Approved
</a>

<a href="?status=rejected"
   class="btn <?= $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
  Rejected
</a>

  </div>

  <?php if ($result->num_rows === 0): ?>
    <p>No users in this category.</p>
  <?php else: ?>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Event</th>
        <th>Date</th>
        <th>Organizer</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>

 <?php while ($row = $result->fetch_assoc()): ?>
<?php
 // Veritabanındaki status değeri büyük/küçük harf veya boşluk içerebilir
    // Karşılaştırmalarda hata olmaması için normalize edilir
    $currentStatus = strtolower(trim($row["status"]));
?>
<tr>
    <td><?= htmlspecialchars($row["title"]) ?></td>
    <td><?= htmlspecialchars($row["date"]) ?></td>
    <td><?= htmlspecialchars($row["username"]) ?></td>
    <td><?= ucfirst($currentStatus) ?></td>
    <td>

        <?php if ($currentStatus === "pending"): ?>
           <!-- Pending eventler için admin hem approve hem reject edebilir -->
            <a href="accept.php?id=<?= $row["event_id"] ?>&from=<?= $status ?>"
               class="btn btn-success btn-sm">Approve</a>

            <a href="reject.php?id=<?= $row["event_id"] ?>&from=<?= $status ?>"
               class="btn btn-danger btn-sm">Reject</a>

        <?php elseif ($currentStatus === "approved"): ?>
            <a href="reject.php?id=<?= $row["event_id"] ?>&from=<?= $status ?>"
               class="btn btn-danger btn-sm">Reject</a>

        <?php elseif ($currentStatus === "rejected"): ?>
            <a href="accept.php?id=<?= $row["event_id"] ?>&from=<?= $status ?>"
               class="btn btn-success btn-sm">Approve</a>
        <?php endif; ?>

    </td>
</tr>
<?php endwhile; ?>


    </tbody>
  </table>

  <?php endif; ?>

  <a href="dashboard.php" class="btn btn-secondary mt-3">Back</a>
</div>

</body>
</html>
