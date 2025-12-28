<?php
//veritabanından sadece onaylanmış etkinlikleri çeker
//frontend'e JSON formatında event listesini döndüren API endpoint
//frontend ve backend arasında veri alışverişi için kullanılır
// Bu dosya bir API endpoint olduğu için response formatını JSON olarak belirtiyoruz
header('Content-Type: application/json');

require_once __DIR__ . '/../assets/includes/db.php';

// Sadece onaylanmış (approved) eventleri listelemek istiyoruz
$status = 'approved';
// Veritabanından gelen eventleri tutmak için boş bir array
$events = [];

// Onaylanmış eventleri tarihe göre artan sırada çeken sorgu
// Prepared statement kullanarak güvenli sorgu çalıştırıyoruz
if ($stmt = $conn->prepare("SELECT event_id, title, artist_name, `date`, 
    location, status, image 
    FROM events WHERE status = ? 
    ORDER BY `date` ASC")) 
    {
        // status parametresi string olduğu için 's' kullanıyoruz
    $stmt->bind_param('s', $status);
    // Sorguyu çalıştırıyoruz
    $stmt->execute();
    $result = $stmt->get_result();
 // Her bir event kaydını array içine ekliyoruz
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
     // Statement kaynağını serbest bırakıyoruz
    $stmt->close();
} else {

    // Sorgu hazırlanamazsa sunucu hatası döndürüyoruz
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement.']);
    exit;
}
// Başarılı durumda event listesini JSON formatında döndürüyoruz
echo json_encode($events);
// Veritabanı bağlantısını kapatıyoruz
$conn->close();