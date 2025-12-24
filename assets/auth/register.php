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
elseif (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password)
) {
    $error = "Password must be at least 8 characters and include upper, lower case letters and a number.";
}

else{
    $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
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
    <title>Register</title>
    <link href="/ctis256_proje/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">

</head>
<body>

<section class="section">
  <div class="container" style="max-width: 480px;">
    <div class="section-title">
      <h2>Register</h2>
      <p>Create your account</p>
    </div>

    <form method="POST" class="php-email-form">

      <div class="mb-3">
        <input type="text" name="username" class="form-control"
               placeholder="Username" required>
      </div>

      <div class="mb-3">
        <input type="email" name="email" class="form-control"
               placeholder="Email" required>
      </div>

      <div class="mb-3">
        <input type="text" name="password" class="form-control"
       placeholder="Password (min 8 chars, A–z, 0–9)"
       
       required>

      </div>

      <div class="mb-3">
        <input type="text" name="confirm" class="form-control"
               placeholder="Confirm Password" required>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox"
               name="organizer_request" id="organizer">
        <label class="form-check-label" for="organizer">
          Request organizer role
        </label>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-danger w-100">
          Register
        </button>
      </div>

      <?php if ($error !== ""): ?>
        <div class="error-message" style="display:block;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

    </form>

    <p class="text-center mt-3">
      Already have an account?
      <a href="login.php">Login</a>
    </p>

  </div>
</section>

</body>

</form>
</body>
</html>