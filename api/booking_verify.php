<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$booking_id = (int)($_POST['booking_id'] ?? 0);
$action     = trim($_POST['action'] ?? '');

if ($booking_id <= 0 || !in_array($action, ['approve','decline'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status='paid', status_id=1 WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE bookings SET payment_status='failed', status_id=3 WHERE id = ?");
    }
    $stmt->execute([$booking_id]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
