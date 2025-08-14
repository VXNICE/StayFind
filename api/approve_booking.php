<?php
header('Content-Type: application/json');
include '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$booking_id = $data['booking_id'];
$status = $data['status']; // approved or rejected

$sql = "UPDATE bookings SET status=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $booking_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Booking status updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update booking"]);
}
?>
