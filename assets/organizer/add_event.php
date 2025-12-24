<?php
session_start();
ini_set('display_errors', 1); error_reporting(E_ALL);

// DB Bağlantısı
require_once "../includes/db.php"; 

// Giriş Kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$organizer_id = $_SESSION['user_id'];

$msg = "";
$errors = [];

// Sticky Values
$title = $artist_name = $descr = $date = $loc = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $artist_name = trim($_POST['artist_name'] ?? '');
    $descr = trim($_POST['descr'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $loc = trim($_POST['loc'] ?? '');
    
    // 1. Resim Yükleme İşlemi
    $image_filename = null; // Değişken adını değiştirdim kafa karışmasın
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newFilename = uniqid("evt_", true) . "." . $ext;
            
            // YOL: assets/organizer -> assets -> CTIS256 -> uploads
            $uploadDir = "../../uploads/";
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                $image_filename = $newFilename; 
            } else {
                $errors[] = "Failed to upload image. Check folder permissions.";
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    }

    if (empty($title) || empty($artist_name) || empty($descr) || empty($date) || empty($loc)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        // DÜZELTME BURADA YAPILDI: image_path yerine 'image' yazdık.
        $sql = "INSERT INTO events (organizer_id, title, artist_name, `desc`, date, location, image, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        if ($stmt = $conn->prepare($sql)) {
            // issssss -> int, string...
            $stmt->bind_param("issssss", $organizer_id, $title, $artist_name, $descr, $date, $loc, $image_filename);
            
            if ($stmt->execute()) {
                $msg = "Event added successfully! Waiting for admin approval.";
                // Formu temizle
                $title = $artist_name = $descr = $date = $loc = "";
            } else {
                $errors[] = "Database Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Prepare Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>

    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .form-label { font-weight: 600; color: #495057; }
        .card { border: none; border-radius: 12px; }
    </style>
</head>
<body>

    <header class="p-3 mb-4 border-bottom bg-white shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <h3 class="m-0 text-dark">Organizer Panel</h3>
            <a href="my_events.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to My Events
            </a>
        </div>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Add New Event</h2>
                    <p class="text-muted">Fill in the details to create a new event request.</p>
                </div>

                <?php if($msg): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= $msg ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach($errors as $e): ?>
                                <li><?= $e ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-5">
                    <div class="card-body p-4 p-md-5">
                        
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                
                                <div class="col-md-12">
                                    <label for="title" class="form-label">Event Title</label>
                                    <input type="text" class="form-control form-control-lg" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="artist" class="form-label">Artist Name</label>
                                    <input type="text" class="form-control" id="artist" name="artist_name" value="<?= htmlspecialchars($artist_name) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="date" class="form-label">Event Date</label>
                                    <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date) ?>" required>
                                </div>

                                <div class="col-md-12">
                                    <label for="loc" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="loc" name="loc" value="<?= htmlspecialchars($loc) ?>" required>
                                </div>

                                <div class="col-md-12">
                                    <label for="image" class="form-label">Event Poster / Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>

                                <div class="col-md-12">
                                    <label for="descr" class="form-label">Description</label>
                                    <textarea class="form-control" id="descr" name="descr" rows="5" required><?= htmlspecialchars($descr) ?></textarea>
                                </div>

                                <div class="col-12 mt-4 d-flex justify-content-between">
                                    <a href="my_events.php" class="btn btn-light text-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                                        Submit Event
                                    </button>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>