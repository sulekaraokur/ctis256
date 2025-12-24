<?php
session_start();
require_once "../includes/db.php";

// Giriş Kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$organizer_id = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Önce resmi bul ki dosyayı da silebilelim (Sütun adı: image)
    $sql = "SELECT image FROM events WHERE event_id = ? AND organizer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if ($event) {
        // 2. Resmi klasörden sil (Çöp oluşmasın)
        // Yol: assets/organizer -> assets -> CTIS256 -> uploads
        $filePath = "../../uploads/" . $event['image'];
        
        if ($event['image'] && file_exists($filePath)) {
            unlink($filePath);
        }
        
        // 3. Veritabanından satırı sil
        $delSql = "DELETE FROM events WHERE event_id = ?";
        $delStmt = $conn->prepare($delSql);
        $delStmt->bind_param("i", $id);
        $delStmt->execute();
    }
}

// İşlem bitince listeye dön
header("Location: my_events.php?deleted=1");
exit;
?>