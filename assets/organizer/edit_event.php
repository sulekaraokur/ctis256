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
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = []; 
$msg = "";

// 1. Mevcut Veriyi Çek (Veritabanı sütununun 'image' olduğuna dikkat et)
$sql = "SELECT * FROM events WHERE event_id = ? AND organizer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found or access denied.");
}

// Form Gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $artist_name = trim($_POST['artist_name']);
    $descr = trim($_POST['descr']);
    $date = trim($_POST['date']);
    $loc = trim($_POST['loc']);
    
    // Varsayılan olarak eski resim kalsın
    $image_filename = $event['image']; 
    
    // Yeni resim yüklendi mi?
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newFilename = uniqid("evt_", true) . "." . $ext;
            $uploadDir = "../../uploads/";
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                // Başarılı yükleme, yeni ismi kaydet
                $image_filename = $newFilename;
                
                // İsteğe bağlı: Eski resmi klasörden silebiliriz (Çöp birikmesin)
                if ($event['image'] && file_exists($uploadDir . $event['image'])) {
                    unlink($uploadDir . $event['image']);
                }
            }
        } else {
            $errors[] = "Invalid file type.";
        }
    }

    if (empty($errors)) {
        // Güncelleme Sorgusu (Sütun adı: image)
        $upSql = "UPDATE events 
                  SET title=?, artist_name=?, `desc`=?, date=?, location=?, image=?, updated_at=NOW() 
                  WHERE event_id=? AND organizer_id=?";
        
        $upStmt = $conn->prepare($upSql);
        // ssssssii -> string x 6, int x 2
        $upStmt->bind_param("ssssssii", $title, $artist_name, $descr, $date, $loc, $image_filename, $id, $organizer_id);
        
        if ($upStmt->execute()) {
            header("Location: my_events.php?updated=1");
            exit;
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <!-- CSS Yolları -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .img-preview { max-width: 150px; border-radius: 8px; border: 1px solid #ddd; padding: 3px; }
    </style>
</head>
<body>

    <header class="p-3 mb-4 border-bottom bg-white shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <h3 class="m-0 text-dark">Organizer Panel</h3>
            <a href="my_events.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Cancel Edit
            </a>
        </div>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Edit Event</h2>
                    <p class="text-muted">Update event details.</p>
                </div>

                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $e) echo "<div>$e</div>"; ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-5">
                    <div class="card-body p-4 p-md-5">
                        
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                
                                <div class="col-md-12">
                                    <label class="form-label">Event Title</label>
                                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Artist Name</label>
                                    <input type="text" class="form-control" name="artist_name" value="<?= htmlspecialchars($event['artist_name']) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Event Date</label>
                                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($event['date']) ?>" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="loc" value="<?= htmlspecialchars($event['location']) ?>" required>
                                </div>

                                <!-- Resim Bölümü -->
                                <div class="col-md-12">
                                    <label class="form-label">Current Image</label><br>
                                    <?php if($event['image']): ?>
                                        <img src="../../uploads/<?= htmlspecialchars($event['image']) ?>" class="img-preview mb-2">
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">No image uploaded.</span>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <label class="form-label small text-muted">Change Image (Optional)</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="descr" rows="5" required><?= htmlspecialchars($event['desc']) ?></textarea>
                                </div>

                                <div class="col-12 mt-4 d-flex justify-content-between">
                                    <a href="my_events.php" class="btn btn-light text-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        Update Event
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