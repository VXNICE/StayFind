<?php
// api/rooms_list.php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

// Detect columns in `rooms` to adapt to your schema
$colsStmt = $pdo->prepare("
  SELECT LOWER(COLUMN_NAME) AS c
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rooms'
");
$colsStmt->execute();
$cols = array_map(fn($r) => $r['c'], $colsStmt->fetchAll(PDO::FETCH_ASSOC));
$has  = fn($c) => in_array(strtolower($c), $cols, true);

$idCol     = $has('id') ? 'id' : ($has('room_id') ? 'room_id' : null);
$titleCol  = $has('title') ? 'title' : ($has('name') ? 'name' : ($has('room_name') ? 'room_name' : null));
$locCol    = $has('location') ? 'location' : ($has('address') ? 'address' : null);
$priceCol  = $has('price_per_night') ? 'price_per_night' : ($has('price') ? 'price' : ($has('rate') ? 'rate' : null));
$capCol    = $has('capacity') ? 'capacity' : null;
$imgCol    = $has('image_path') ? 'image_path' : ($has('photo') ? 'photo' : null);

$selects = [];
$selects[] = $idCol     ? "`$idCol` AS id"        : "NULL AS id";
$selects[] = $titleCol  ? "`$titleCol` AS title"  : "'' AS title";
$selects[] = $locCol    ? "`$locCol` AS location" : "'' AS location";
$selects[] = $priceCol  ? "`$priceCol` AS price"  : "NULL AS price";
$selects[] = $capCol    ? "`$capCol` AS capacity" : "NULL AS capacity";
$selects[] = $imgCol    ? "`$imgCol` AS image"    : "'' AS image";

$sql = "SELECT " . implode(", ", $selects) . " FROM rooms ORDER BY 1 DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();

echo json_encode(['success' => true, 'rooms' => $rows]);
