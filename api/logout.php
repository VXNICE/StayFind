<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$_SESSION = [];
session_destroy();
echo json_encode(['ok' => true]);
