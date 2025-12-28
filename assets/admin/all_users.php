<?php
//usersları listeler
//admin paneli için

session_start();
require_once __DIR__ . '/../includes/db.php';

// Sadece admin kullanıcıların bu sayfaya erişebilmesi için kontrol yapıyoruz
// Yetkisiz erişim durumunda ana sayfaya yönlendirme yapılıyor

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /ctis256_proje/index.php');
    exit;
}

// Sistemde kayıtlı tüm kullanıcıları listelemek için sorgu
// created_at alanına göre sıralama yaparak en yeni kullanıcıları üstte gösteriyoruz
$result = $conn->query(
    'SELECT username, email, role FROM users ORDER BY created_at DESC'
);
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];


// Kullanıcının rolüne göre ekranda gösterilecek badge'i döndüren yardımcı fonksiyon
// UI mantığını HTML'den ayırmak için fonksiyon olarak tanımlandı
function displayStatus(string $role): string
{
    if ($role === 'admin') {
        return '<span class="badge bg-primary-subtle text-primary">Admin</span>';
    }
    if ($role === 'organizer') {
        return '<span class="badge bg-success-subtle text-success">Organizer</span>';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
    <link href="/ctis256_proje/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
</head>
<body class="bg-light">
<section class="section">
    <div class="container">
        <div class="section-title d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h2 class="mb-1">All Users</h2>
                <p class="text-muted mb-0">View registered users</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary">&larr; Back to Dashboard</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Users</h5>
                    <span class="badge bg-primary-subtle text-primary">Total: <?= count($users) ?></span>
                </div>

                <?php if (empty($users)): ?>
                    <p class="mb-0">No users found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 70px;">#</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <th scope="row"><?= $index + 1 ?></th>
                                        <td><?= $user['username'] ?></td>
                                        <td><?= $user['email']?></td>
                                        <td><?= displayStatus($user['role']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
</body>
</html>