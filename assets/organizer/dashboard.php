<?php
session_start();

/* SADECE ORGANIZER GİREBİLİR */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Dashboard</title>

    <link href="/ctis256_proje/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
</head>
<body>

<section class="section">
    <div class="container">

        <div class="section-title">
            <h2>Organizer Dashboard</h2>
            <p>Manage your events</p>
        </div>

        <div class="text-center mb-4">
            <h5>
                Welcome,
                <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Organizer') ?></strong>
            </h5>
        </div>

        <div class="row gy-4 justify-content-center">

            <!-- ADD EVENT -->
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Add Event</h5>
                        <p>Create a new event</p>
                        <a href="add_event.php" class="btn btn-danger">
                            Add Event
                        </a>
                    </div>
                </div>
            </div>

            <!-- MY EVENTS -->
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">My Events</h5>
                        <p>Edit or delete your events</p>
                        <a href="my_events.php" class="btn btn-outline-danger">
                            View Events
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-5">
            <a href="../../index.php" class="btn btn-secondary">
                Back to Home
            </a>
        </div>

    </div>
</section>

</body>
</html>
