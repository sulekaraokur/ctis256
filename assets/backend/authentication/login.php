<?php
session_start();
include "../includes/db.php";
$error= "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";


$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();


    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["role"] = $role;
            
            if ($role === "admin") {
            header("Location: admin_home.php"); //pathi sonra değiş
            exit;
            }
        
            if ($role === "organizer") {
                header("Location: organizer_home.php");
                exit;
            }

            if ($role === "attendee") {
                header("Location: attendee_home.php");
                exit;
            }

        } else {
            $error = "error: wrong password";
        }
    } else {
        $error = "error: user not found";
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
    <form method="POST" action="">
    <label>Username:</label>
    <input type="text" name="username" required><br>

    <label>Password:</label>
    <input type="password" name="password" required><br>

    <button type="submit">Login</button>
</form>

<?php if (!empty($error)) echo "<p>$error</p>"; ?>
</body>
</html>