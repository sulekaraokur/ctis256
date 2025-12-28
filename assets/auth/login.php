<?php
session_start();
// Hata raporlamayı aç
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Veritabanı yoluna dikkat et! (Senin dosya yapına göre ayarladım)
require_once "../includes/db.php"; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Projende 'username' sütunu yok, 'email' var. O yüzden email kullanıyoruz.
    $email = trim($_POST["username"] ?? ""); // Formda name="username" kalsa bile biz bunu email olarak alalım
    $password = trim($_POST["password"] ?? "");

    // SQL Sorgusu (email'e göre arama)
    $sql = "SELECT user_id, password, role FROM users WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // IDE hatasını önlemek için değişkenleri başta tanımlayalım
            $id = 0;
            $hashed = "";
            $role = "";

            // Değişkenleri eşleştir
            $stmt->bind_result($id, $hashed, $role);
            $stmt->fetch();

            // Şifre Kontrolü
            if (password_verify($password, $hashed)) {
                // Giriş Başarılı
                $_SESSION["user_id"] = $id;
                $_SESSION["role"] = $role;
                $_SESSION["username"] = $email; // İsim olarak emaili saklayalım

                // Yönlendirmeler
                if ($role === "admin") {
                    header("Location: ../../assets/admin/dashboard.php"); 
                } elseif ($role === "organizer") {
                    header("Location: ../../assets/organizer/my_events.php");
                } else {
                    // Normal user
                    header("Location: ../../index.php");
                }
                exit;

            } else {
                $error = "Hatalı şifre!";
            }
        } else {
            $error = "Bu email adresiyle kayıtlı kullanıcı bulunamadı.";
        }
        $stmt->close();
    } else {
        $error = "Veritabanı hatası: " . $conn->error;
    }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- CSS Dosyaları -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
</head>
<body>

<section class="section d-flex align-items-center" style="min-height: 100vh; background-color: #f6f9ff;">
  <div class="container" style="max-width: 400px;">
    
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <div class="section-title text-center mb-4">
              <h2 class="fw-bold" style="color:var(--heading-color)">Login</h2>
              <p class="text-muted">Sign in to your account</p>
            </div>

            <!-- Form -->
            <form method="POST" class="php-email-form">

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <!-- Veritabanında email olduğu için type="email" yaptık -->
                 <!-- sticky form -->
                <input 
                  type="email" 
                  name="username" 
                  class="form-control" 
                  placeholder="name@example.com"
                  value="<?= htmlspecialchars($email ?? '') ?>"
                   required
                  >
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" style="background-color: var(--accent-color); border:none;">Login</button>
              </div>
              
              <div class="text-center mt-3">
                  <small>Don't have an account? <a href="register.php">Register</a></small>
              </div>

            </form>
        </div>
    </div>

  </div>
</section>

</body>
</html>