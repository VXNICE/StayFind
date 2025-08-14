<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
$_SESSION = [];
session_destroy();
echo json_encode(['success' => true, 'message' => 'Logged out']);
