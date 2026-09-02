<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$productId = intval($_POST['product_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if (!in_array($action, ['approved', 'rejected']) || $productId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE products SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $action, $productId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'status' => $action]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Failed']);
}
?>
