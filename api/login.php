<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = strtolower(trim($input['email'] ?? ''));
$pass = $input['password'] ?? '';

$stmt = $pdo->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || !password_verify($pass, $user['password_hash'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Invalid credentials']);
  exit;
}
set_user_session($user);
echo json_encode(['ok' => true, 'user' => $_SESSION['user']]);
