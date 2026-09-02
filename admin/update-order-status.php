<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$orderId = intval($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';
$allowed = ['placed','confirmed','processing','shipped','delivered','cancelled'];

if ($orderId <= 0 || !in_array($status, $allowed)) {
    echo json_encode(['success' => false]);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed or order not found']);
}
?>
