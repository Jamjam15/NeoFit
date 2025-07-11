<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$order_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate status
$valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Update order status
$sql = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $order_id);

$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Status updated successfully' : 'Failed to update status'
]);
?> 