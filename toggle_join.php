<?php
session_start();
// Veritabanı dosyasının yoluna dikkat et!
    require_once "assets/includes/db.php"; 

if (!isset($_SESSION['user_id'])) {
    // Giriş yapmamışsa login sayfasına at
    header("Location: assets/auth/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$action = $_GET['action'] ?? '';

if ($event_id > 0) {
    if ($action === 'join') {
        // Zaten kayıtlı mı kontrol et (MySQLi)
        $checkStmt = $conn->prepare("SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1");
        $checkStmt->bind_param("ii", $user_id, $event_id);
        $checkStmt->execute();
        $checkStmt->store_result();
         if ($checkStmt->num_rows === 0) { 
            // Kayıt Ekle (MySQLi Prepared)
             $insertStmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, registered_at) VALUES (?, ?, NOW())");
            $insertStmt->bind_param("ii", $user_id, $event_id);
            $insertStmt->execute();
        
    } elseif ($action === 'cancel') {
        // Kaydı Sil (MySQLi Prepared)
       $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
    }
}

// İşlem bitince ana sayfaya geri dön
header("Location: index.php");
exit;
}
?>