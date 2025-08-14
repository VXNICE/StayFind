<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $q = '%' . trim($_GET['q'] ?? '') . '%';
  $stmt = $pdo->prepare('SELECT r.*, u.name as owner_name FROM rooms r JOIN users u ON r.owner_id=u.id WHERE r.title LIKE ? OR r.location LIKE ? ORDER BY r.created_at DESC');
  $stmt->execute([$q, $q]);
  echo json_encode($stmt->fetchAll());
  exit;
}

if ($method === 'POST') {
  require_role(['owner','admin']);
  $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
  $title = trim($data['title'] ?? '');
  $price = (float)($data['price_per_night'] ?? 0);
  if (!$title || $price <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid input']);
    exit;
  }
  $stmt = $pdo->prepare('INSERT INTO rooms (owner_id,title,description,price_per_night,location,capacity,image_path) VALUES (?,?,?,?,?,?,?)');
  $stmt->execute([$_SESSION['user']['id'], $title, $data['description'] ?? null, $price, $data['location'] ?? null, (int)($data['capacity'] ?? 1), $data['image_path'] ?? null]);
  echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
  exit;
}

http_response_code(405);
