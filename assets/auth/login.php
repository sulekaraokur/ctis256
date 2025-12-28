<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php"; 

$error = "";
$entered_email = ""; // Sticky form için değişken

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["username"] ?? ""); 
    $password = trim($_POST["password"] ?? "");
    
    // Sticky form: Hata olsa bile email kutuda kalsın diye değişkene atıyoruz
    $entered_email = $email;

    // SORGUMUZU GÜNCELLİYORUZ: is_approved sütununu da çekiyoruz!
    // EĞER VERİTABANINDA BU SÜTUNUN ADI FARKLIYSA (örn: approved, status) BURAYI DÜZELT!
    $sql = "SELECT user_id, password, role, is_approved FROM users WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $id = 0;
            $hashed = "";
            $role = "";
            $is_approved = 0; // Varsayılan değer

            // Sonuçları değişkenlere bağla
            $stmt->bind_result($id, $hashed, $role, $is_approved);
            $stmt->fetch();

            if (password_verify($password, $hashed)) {
                
                // --- KRİTİK KONTROL BAŞLIYOR ---
                // Eğer kullanıcı Organizatör ise VE onayı yoksa (0 ise)
                if ($role === 'organizer' && $is_approved == 0) {
                    $error = "Hesabınız henüz Admin tarafından onaylanmadı. Lütfen bekleyiniz.";
                } 
                else {
                    // Giriş Başarılı (Admin, Normal User veya Onaylı Organizatör)
                    $_SESSION["user_id"] = $id;
                    $_SESSION["role"] = $role;
                    $_SESSION["username"] = $email;

                    if ($role === "admin") {
                        header("Location: ../../assets/admin/dashboard.php"); 
                    } elseif ($role === "organizer") {
                        header("Location: ../../assets/organizer/my_events.php");
                    } else {
                        header("Location: ../../index.php");
                    }
                    exit;
                }
                // --- KONTROL BİTTİ ---

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

            <form method="POST" class="php-email-form">

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label class="form-label">Email</label>
                <!-- STICKY FORM GÜNCELLEMESİ: value kısmına bak -->
                <input type="email" name="username" class="form-control" 
                       placeholder="name@example.com" required 
                       value="<?= htmlspecialchars($entered_email) ?>">
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