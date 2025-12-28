<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../assets/includes/db.php';

$status = 'approved';

$events = [];

if ($stmt = $conn->prepare("SELECT event_id, title, artist_name, `date`, 
    location, status, image 
    FROM events WHERE status = ? 
    ORDER BY `date` ASC")) 
    {
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement.']);
    exit;
}

echo json_encode($events);

$conn->close();