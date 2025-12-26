<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../assets/includes/db.php';

$organizerId = filter_input(INPUT_GET, 'organizer_id', FILTER_VALIDATE_INT);

if ($organizerId === null || $organizerId === false) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid organizer_id is required.']);
    exit;
}

$status = 'approved';
$events = [];

$query = "SELECT event_id, title, artist_name, `date`, location, status, image 
          FROM events 
          WHERE status = ? AND organizer_id = ?
          ORDER BY `date` ASC";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param('si', $status, $organizerId);
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