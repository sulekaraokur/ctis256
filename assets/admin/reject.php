<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: /ctis256_proje/index.php");
    exit;
}

$id = $_GET["id"] ?? null;
if (!$id) {
    header("Location: waiting_list.php");
    exit;
}

$stmt = $conn->prepare(
    "UPDATE users
     SET organizer_request = 'rejected',
         role = 'user'
     WHERE user_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$from = $_GET["from"] ?? "pending";
header("Location: waiting_list.php?status=" . $from);
exit;

