<?php
session_start();
// Veritabanı dosyasının yoluna dikkat et!
require_once "assets/backend/includes/db.php"; 

if (!isset($_SESSION['user_id'])) {
    // Giriş yapmamışsa login sayfasına at
    header("Location: assets/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if ($event_id > 0) {
    if ($action === 'join') {
        // Zaten kayıtlı mı kontrol et (MySQLi)
        $checkSql = "SELECT * FROM registrations WHERE user_id = $user_id AND event_id = $event_id";
        $check = $conn->query($checkSql);

        if ($check->num_rows == 0) {
            // Kayıt Ekle (MySQLi Prepared)
            $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, registered_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
        }
    } elseif ($action === 'cancel') {
        // Kaydı Sil (MySQLi Prepared)
        $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id=? AND event_id=?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
    }
}

// İşlem bitince ana sayfaya geri dön
header("Location: index.php");
exit;
?>