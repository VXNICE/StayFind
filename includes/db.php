<?php
// includes/db.php
declare(strict_types=1);
session_start();

$env = [];
if (file_exists(__DIR__ . '/../.env')) {
  foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $env[$k] = $v;
  }
}

$DB_HOST = $env['DB_HOST'] ?? '127.0.0.1';
$DB_NAME = $env['DB_NAME'] ?? 'stayfind_db';
$DB_USER = $env['DB_USER'] ?? 'root';
$DB_PASS = $env['DB_PASS'] ?? '';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];
try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}

// Basic CORS for local dev (adjust in prod)
header('Access-Control-Allow-Origin: ' . ($env['APP_URL'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
