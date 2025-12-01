<?php
session_start();
include "../includes/db.php";
$error="";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

$username   = trim($_POST["username"] ?? "");
$email      = trim($_POST["email"] ?? "");
$password   = $_POST["password"] ?? "";
$confirm    = $_POST["confirm"] ?? ""; //confirm password
$organizer_request  = isset($_POST["organizer_request"]) ? "pending" : "none"; 

if ($username === "" || $email === "" || $password === "" || $confirm === "") {
    $error = "error: please fill every field";
}
else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "error: invalid email";
}
else if ($password !== $confirm) {
    $error = "error: passwords do not match";
}
else{
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "error: username already taken";}
    else{
        $hashed = password_hash($password, PASSWORD_DEFAULT);

            
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password, organizer_request)
                VALUES (?, ?, ? ,?)" 
                
            );
            
            //sql değişikliği: organizer_request eklenmeli, role default "attendee" olmalı
             
            
            $stmt->bind_param("ssss", $username, $email, $hashed, $organizer_request);

             if ($stmt->execute()) {
                header("Location: login.php");
                 exit;
            }
            else {
                $error = "error: can't register";

    }

    

}   
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST">

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm" required><br><br>

    <label>
        <input type="checkbox" name="organizer_request"> organizer request
    </label><br><br>

    <button type="submit">Register</button>

    <?php 
    if ($error !== "") {
        echo "<p style='color:red'>$error</p>";
    }
?>

</form>
</body>
</html>