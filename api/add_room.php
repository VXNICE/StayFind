<?php
require_once '../db.php'; // include your DB connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$owner_id = $_POST['owner_id'] ?? '';
$title = $_POST['title'] ?? '';
$location = $_POST['location'] ?? '';
$type = $_POST['type'] ?? '';
$price = $_POST['price'] ?? '';
$description = $_POST['description'] ?? '';
$image_path = '';

if (
    empty($owner_id) || empty($title) || empty($location) || empty($type)
    || empty($price) || !isset($_FILES['image'])
) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit;
}

// Upload image
$upload_dir = '../uploads/';
$image_name = uniqid() . '_' . basename($_FILES['image']['name']);
$target_path = $upload_dir . $image_name;

if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
    $image_path = 'uploads/' . $image_name;
} else {
    echo json_encode(['success' => false, 'message' => 'Image upload failed']);
    exit;
}

// Save to database
$stmt = $conn->prepare("INSERT INTO rooms (owner_id, title, location, type, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssiss", $owner_id, $title, $location, $type, $price, $description, $image_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
