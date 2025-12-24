<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: /ctis256_proje/index.php");
    exit;
}

/* FILTER */
$status = $_GET["status"] ?? "pending";
$allowed = ["pending","approved","rejected"];
if (!in_array($status, $allowed)) {
    $status = "pending";
}

$stmt = $conn->prepare(
    "SELECT user_id, username, email, organizer_request
     FROM users
     WHERE organizer_request = ?"
);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Organizer Requests</title>
  <link href="/ctis256_proje/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
        <th>Username</th>
        <th>Email</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>

    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row["username"]) ?></td>
        <td><?= htmlspecialchars($row["email"]) ?></td>
        <td><?= ucfirst($row["organizer_request"]) ?></td>
        <td>

          <?php if ($row["organizer_request"] !== "approved"): ?>
            <a href="accept.php?id=<?= $row["user_id"] ?>"
               class="btn btn-success btn-sm">
               Approve
            </a>
          <?php endif; ?>

          <?php if ($row["organizer_request"] !== "rejected"): ?>
            <a href="reject.php?id=<?= $row["user_id"] ?>"
               class="btn btn-danger btn-sm">
               Reject
            </a>
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
