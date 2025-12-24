<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "concert_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("connection error: " . $conn->connect_error);
}
