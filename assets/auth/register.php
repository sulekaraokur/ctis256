<?php
// Hataları göster
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Dosya yolu (Senin yapına göre assets/includes/db.php)
require_once "../includes/db.php"; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Veritabanında 'username' olduğu için formdan gelen veriyi username olarak alıyoruz
    $username = trim($_POST["username"] ?? ""); 
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm"] ?? "";
    
    // Checkbox kontrolü
    $is_organizer = isset($_POST["organizer_request"]);

    // Validasyonlar
    if ($username === "" || $email === "" || $password === "" || $confirm === "") {
        $error = "Error: Please fill every field";
    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Error: Invalid email format";
    }
    else if ($password !== $confirm) {
        $error = "Error: Passwords do not match";
    }
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    }
    else {
        // 1. Email kontrolü
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if (!$check) { die("DB Error (Check): " . $conn->error); }
        
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Error: Email already registered.";
        }
        else {
            // Şifreleme
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Rol ve Onay Durumu Belirleme
            if ($is_organizer) {
                $role = 'organizer';
                $is_approved = 0; // Admin onayı bekleyecek
            } else {
                $role = 'user';
                $is_approved = 1; // Direkt onaylı
            }

            // 2. Kayıt Ekleme (DÜZELTİLDİ: full_name yerine username yazıldı)
            $sql = "INSERT INTO users (username, email, password, role, is_approved) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) { 
                // Eğer yine hata alırsan, sütun adlarını görmek için hatayı ekrana basar
                die("DB Error (Prepare): " . $conn->error); 
            }

            // ssssi -> username(s), email(s), password(s), role(s), is_approved(i)
            $stmt->bind_param("ssssi", $username, $email, $hashed, $role, $is_approved);

            if ($stmt->execute()) {
                // Kayıt başarılı, login'e yönlendir
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Error: Database insertion failed - " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- CSS Yolları -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
</head>
<body>

<section class="section d-flex align-items-center" style="min-height: 100vh; background-color: #f6f9ff;">
  <div class="container" style="max-width: 480px;">
    
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="section-title text-center mb-4">
              <h2>Register</h2>
              <p>Create your account</p>
            </div>

            <form method="POST" class="php-email-form">

              <?php if ($error !== ""): ?>
                <div class="alert alert-danger">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Username</label>
                <!-- DÜZELTİLDİ: name="username" yapıldı -->
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min 8 chars" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm" class="form-control" placeholder="Retype password" required>
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="organizer_request" id="organizer">
                <label class="form-check-label" for="organizer">
                  I want to be an Event Organizer
                </label>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-danger" style="background-color: var(--accent-color); border:none;">
                  Register
                </button>
              </div>

            </form>

            <p class="text-center mt-3">
              Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>

  </div>
</section>

</body>
</html>