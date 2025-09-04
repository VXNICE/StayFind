<?php
// api/bookings_create.php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$room_id    = (int)($_POST['room_id'] ?? 0);
$start_date = trim($_POST['start_date'] ?? '');
$end_date   = trim($_POST['end_date'] ?? '');
$guests     = (int)($_POST['guests'] ?? 1);

if ($room_id <= 0 || $start_date === '' || $end_date === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

if ($start_date > $end_date) {
    echo json_encode(['success' => false, 'message' => 'Start date must be before end date.']);
    exit;
}

try {
    // check availability: no overlapping confirmed or pending bookings
    $q = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status_id IN (1,2) AND NOT (end_date < ? OR start_date > ?)");
    $q->execute([$room_id, $start_date, $end_date]);
    if ($q->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Room not available for selected dates.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO bookings (room_id, user_id, start_date, end_date, guests, status_id, payment_status) VALUES (?, ?, ?, ?, ?, 2, 'unpaid')");
    $stmt->execute([$room_id, $_SESSION['user']['id'], $start_date, $end_date, $guests]);
    $id = (int)$pdo->lastInsertId();
    echo json_encode(['success' => true, 'booking_id' => $id]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
