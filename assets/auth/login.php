<?php
session_start();
include "../includes/db.php";
$error= "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";


$stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();


    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["role"] = $role;
            $_SESSION["username"] = $username;

            
            if ($role === "admin") {
            header("Location: ../../index.php"); 
            exit;
            }
        
            if ($role === "organizer") {
                header("Location: ../../index.php");
                exit;
            }

            if ($role === "user") {
                header("Location: ../../index.php");
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
    <title>Login</title>
</head>
<body>

<section class="section">
  <div class="container" style="max-width: 420px;">
    <div class="section-title">
      <h2>Login</h2>
      
      <link href="../css/main.css" rel="stylesheet">
      <link href="/ctis256_proje/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">


    </div>

    <form method="POST" class="php-email-form">

      <div class="mb-3">
        <input type="text" name="username" class="form-control"
               placeholder="Username" required>
      </div>

      <div class="mb-3">
        <input type="password" name="password" class="form-control"
               placeholder="Password" required>
      </div>

      <div class="text-center">
        <button type="submit">Login</button>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-message" style="display:block;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

    </form>
    <p class="text-center mt-3">
  Don’t have an account?
  <a href="register.php" >
    Register
  </a>
</p>




  </div>
  
</section>

</body>
</html>