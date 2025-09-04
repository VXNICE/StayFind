<?php
// api/booking_payment_upload.php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$user = $_SESSION['user'];
$booking_id = (int)($_POST['booking_id'] ?? 0);
$method = trim($_POST['method'] ?? '');
$reference = trim($_POST['reference'] ?? '');

if ($booking_id <= 0 || $method === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

try {
    // Verify booking exists and user owns it or admin
    $stmt = $pdo->prepare("SELECT user_id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    if ($book['user_id'] != $user['id'] && ($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }

    $path = null;
    if (!empty($_FILES['receipt']['name'])) {
        $allowed = ['image/jpeg','image/png','application/pdf'];
        if (!in_array($_FILES['receipt']['type'], $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }
        if ($_FILES['receipt']['size'] > 5*1024*1024) {
            echo json_encode(['success' => false, 'message' => 'File too large']);
            exit;
        }
        $ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $fname = bin2hex(random_bytes(8)) . '.' . $ext;
        $destDir = __DIR__ . '/../uploads/payments';
        if (!is_dir($destDir)) { mkdir($destDir, 0775, true); }
        $dest = $destDir . '/' . $fname;
        if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $dest)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            exit;
        }
        $path = 'uploads/payments/' . $fname;
    }

    $stmt = $pdo->prepare("UPDATE bookings SET payment_method=?, payment_reference=?, payment_receipt_path=?, payment_status='pending' WHERE id = ?");
    $stmt->execute([$method, $reference, $path, $booking_id]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
