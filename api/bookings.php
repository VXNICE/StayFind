<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
  require_role(['user','owner','admin']);
  $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
  $room_id = (int)($d['room_id'] ?? 0);
  $start = $d['start_date'] ?? null;
  $end   = $d['end_date'] ?? null;

  if (!$room_id || !$start || !$end || strtotime($end) <= strtotime($start)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid dates']);
    exit;
  }

  // Check overlap
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id=? AND status IN ('pending','approved') AND NOT (end_date<=? OR start_date>=?)");
  $stmt->execute([$room_id, $start, $end]);
  if ($stmt->fetchColumn() > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Dates not available']);
    exit;
  }

  $stmt = $pdo->prepare('INSERT INTO bookings (room_id,user_id,start_date,end_date) VALUES (?,?,?,?)');
  $stmt->execute([$room_id, $_SESSION['user']['id'], $start, $end]);
  echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
  exit;
}

if ($method === 'GET') {
  require_login();
  $mine = ($_GET['mine'] ?? '') === '1';
  if ($mine) {
    $stmt = $pdo->prepare('SELECT b.*, r.title FROM bookings b JOIN rooms r ON b.room_id=r.id WHERE b.user_id=? ORDER BY b.created_at DESC');
    $stmt->execute([$_SESSION['user']['id']]);
    echo json_encode($stmt->fetchAll());
  } else {
    $roomId = (int)($_GET['room_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE room_id=? ORDER BY created_at DESC');
    $stmt->execute([$roomId]);
    echo json_encode($stmt->fetchAll());
  }
  exit;
}

http_response_code(405);
