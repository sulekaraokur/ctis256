<?php
//Bir kullanıcının bir evente katılma (join) veya katılımını iptal etme (cancel) işlemini yönetmek
//kayıtlı mı değil mi(on/off) -> toggle

session_start();


// Veritabanı bağlantısını dahil ediyoruz
    require_once "assets/includes/db.php"; 

if (!isset($_SESSION['user_id'])) {

    // Kullanıcı giriş yapmamışsa bu işlemleri yapmasına izin vermiyoruz

    header("Location: assets/auth/login.php");
    exit;
}

// Giriş yapan kullanıcının ID bilgisi
$user_id = (int) $_SESSION['user_id'];
// URL üzerinden gelen event ID (geçerli integer değilse 0 olur)
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

// Yapılacak işlem: join veya cancel
$action = $_GET['action'] ?? '';


// Geçerli bir event ID varsa işlem yapılır
if ($event_id > 0) {


    // Kullanıcı bir evente katılmak istiyorsa
    if ($action === 'join') {
        
        // Kullanıcı daha önce bu evente kayıt olmuş mu kontrol ediyoruz
        $checkStmt = $conn->prepare
        ("SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1");

        $checkStmt->bind_param("ii", $user_id, $event_id);
        $checkStmt->execute();
        $checkStmt->store_result();

         // Eğer kayıt yoksa yeni kayıt ekleniyor
         if ($checkStmt->num_rows === 0) { 
            // Kayıt Ekle (MySQLi Prepared)

             $insertStmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, registered_at) 
             VALUES (?, ?, NOW())");
            $insertStmt->bind_param("ii", $user_id, $event_id);
            $insertStmt->execute();

         }
        // Kullanıcı event kaydını iptal etmek istiyorsa
    } elseif ($action === 'cancel') {
        
        // Kullanıcının ilgili event için yaptığı kayıt siliniyor
       $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
    }
}

// İşlem tamamlandıktan sonra ana sayfaya yönlendiriyoruz
header("Location: index.php");
exit;

?>