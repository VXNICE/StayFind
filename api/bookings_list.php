<?php
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
$role = $user['role'] ?? 'user';

try {
    if ($role === 'admin') {
        $sql = "SELECT b.*, r.title AS room_title, u.name AS user_name, u.email AS user_email
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC";
        $stmt = $pdo->query($sql);
    } else {
        $sql = "SELECT b.*, r.title AS room_title
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id']]);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'bookings' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
