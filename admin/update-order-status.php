<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$orderId = intval($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';
$allowed = ['placed','confirmed','processing','shipped','delivered','cancelled'];

if ($orderId <= 0 || !in_array($status, $allowed)) {
    echo json_encode(['success' => false]);
    exit();
}

mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $orderId");
echo json_encode(['success' => true]);
?>
