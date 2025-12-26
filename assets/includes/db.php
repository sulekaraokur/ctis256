<?php
// includes/db.php

$servername = "localhost";
$username   = "root";     // Kendi kullanıcı adın
$password   = "root";         // Kendi şifren
$dbname     = "concert_db"; // Veritabanı adın

// MySQLi Bağlantısı Oluşturma
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı Kontrolü
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Türkçe karakter sorunu olmaması için
$conn->set_charset("utf8mb4");
?>