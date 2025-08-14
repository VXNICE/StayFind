<?php
header('Content-Type: application/json');
include '../includes/db.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';
$role = 'client';

if (!$name || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "All required fields must be filled."]);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already registered."]);
    exit;
}
$stmt->close();

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $hashed, $phone, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Registration successful."]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);

}
$stmt->close();
$conn->close();
?>
