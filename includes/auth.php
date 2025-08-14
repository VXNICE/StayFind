<?php
// includes/auth.php
declare(strict_types=1);

function require_login(): void {
  if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
  }
}

function require_role(array $roles): void {
  require_login();
  if (!in_array($_SESSION['user']['role'] ?? 'user', $roles, true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
  }
}

function set_user_session(array $user): void {
  $_SESSION['user'] = [
    'id' => (int)$user['id'],
    'email' => $user['email'],
    'role' => $user['role'],
    'name' => $user['name'] ?? ''
  ];
  session_regenerate_id(true);
}
