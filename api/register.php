<?php
require_once __DIR__ . '/../db.php'; // path to your db.php

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($name) || empty($email) || empty($password)) {
    die("Please fill all required fields.");
}

try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $hashedPassword, $phone]);

    echo "Registration successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
