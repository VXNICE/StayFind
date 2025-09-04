<?php
session_start();
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Bookings</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-50">
  <h1 class="text-2xl font-bold mb-4">Pending Bookings</h1>
  <table class="min-w-full bg-white border" id="bookingsTable">
    <thead class="bg-gray-100">
      <tr>
        <th class="p-2 border">ID</th>
        <th class="p-2 border">User</th>
        <th class="p-2 border">Room</th>
        <th class="p-2 border">Dates</th>
        <th class="p-2 border">Payment</th>
        <th class="p-2 border">Actions</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
  <script src="assets/js/admin_bookings.js"></script>
</body>
</html>
