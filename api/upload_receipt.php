<?php
header('Content-Type: application/json');
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt'])) {
    $booking_id = $_POST['booking_id'];
    $file = $_FILES['receipt'];
    $target_dir = "../uploads/";
    $filename = uniqid() . "_" . basename($file["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $sql = "UPDATE bookings SET receipt=?, status='waiting_approval' WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $filename, $booking_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Receipt uploaded"]);
        } else {
            echo json_encode(["success" => false, "message" => "Database update failed"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Upload failed"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
