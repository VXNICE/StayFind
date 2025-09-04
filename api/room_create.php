<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'] ?? '', ['admin','owner'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$title = trim($_POST['title'] ?? '');
$location = trim($_POST['location'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$capacity = (int)($_POST['capacity'] ?? 1);
$description = trim($_POST['description'] ?? '');
$image = trim($_POST['image'] ?? '');

if ($title === '' || $location === '' || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO rooms (title, location, price, capacity, description, image) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$title, $location, $price, $capacity, $description, $image]);
    echo json_encode(['success' => true, 'room_id' => $pdo->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
