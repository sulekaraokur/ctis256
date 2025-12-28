<?php
//Kullanıcı rolüne göre hangi eventlerin görünür olduğunu belirliyor, 
// sadece onaylanmış verileri alıyor ve 
// güvenli şekilde JSON formatında iletişim sağlıyor.


// Bu dosya bir API endpoint olduğu için response formatını JSON olarak ayarlıyoruz
//aynı api farklı, farklı kullanıcıya farklı veriler

header('Content-Type: application/json');

require_once __DIR__ . '/../assets/includes/db.php';


// URL üzerinden gelen organizer_id parametresini alıyoruz
// FILTER_VALIDATE_INT ile geçerli bir integer olup olmadığını kontrol ediyoruz
//(SQL Injection riskini azaltmak için)
//normalde extract($_GET) kullanıyoruz ancak burda güvenlik için kullanmıyoruz
//buna alternatif olarak filter_input kullanıyoruz
$organizerId = filter_input(INPUT_GET, 'organizer_id', FILTER_VALIDATE_INT);


// organizer_id eksik ya da geçersizse 400 Bad Request döndürüyoruz
if ($organizerId === null || $organizerId === false) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid organizer_id is required.']);
    exit;
}
// Sadece onaylanmış (approved) eventleri listelemek istiyoruz
$status = 'approved';
$events = [];

// Organizer'a ait ve onaylanmış eventleri çekiyoruz
// Tarihe göre artan sıralama yaparak en yakın etkinlikleri üstte gösteriyoruz
$query = "SELECT event_id, title, artist_name, `date`, location, status, image 
          FROM events 
          WHERE status = ? AND organizer_id = ?
          ORDER BY `date` ASC";

// Prepared statement kullanarak SQL Injection riskini önlüyoruz
if ($stmt = $conn->prepare($query)) {

     // status string, organizer_id integer olduğu için 'si' kullanıyoruz
    $stmt->bind_param('si', $status, $organizerId);
    $stmt->execute();
    $result = $stmt->get_result();
// Veritabanından gelen her eventi array içine ekliyoruz
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
// Başarılı durumda event listesini JSON olarak döndürüyoruz
echo json_encode($events);
// Veritabanı bağlantısını kapatıyoruz
$conn->close();