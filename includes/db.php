<?php
// includes/db.php
// Simple PDO connection to your MySQL

$host = "127.0.0.1";
$dbname = "stayfind_db";   // ← your DB name
$username = "root";        // ← your MySQL user
$password = "";            // ← your MySQL password (leave empty if none)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    // Return clean JSON error if DB fails
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
