<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";

// Şimdilik sabit organizer id (login sonrası session'dan alınacak)
$organizer_id = 1;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: my_events.php");
    exit;
}

try {
    // Sadece kendi eventini silebilsin
    $sql = "DELETE FROM events WHERE event_id = ? AND organizer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $organizer_id]);

    header("Location: my_events.php?deleted=1");
    exit;
} catch (Exception $ex) {
    die("DB error: " . $ex->getMessage());
}
