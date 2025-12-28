<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
</head>
<section class="section">
    <div class="container">

        <div class="section-title">
            <h2>Admin Dashboard</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?></p>
        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="card text-center p-4">
                    <h5>Organizer Requests</h5>
                    <p>Users waiting for approval</p>
                    <a href="waiting_list.php" class="btn btn-outline-danger">
                        View Requests
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-4">
                    <h5>All Users</h5>
                    <p>View registered users</p>
                    <a href="all_users.php" class="btn btn-outline-danger">
                        View Users
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center p-4">
                    <h5>Events</h5>
                    <p>Manage events</p>
                    <a href="all_events.php" class="btn btn-outline-danger">
                        Coming soon
                    </a>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <a href="../../index.php" class="btn btn-secondary">
                Back to Home
            </a>
        </div>

    </div>
</section>

</body>
</html>
