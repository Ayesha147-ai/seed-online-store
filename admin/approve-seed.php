<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$productId = intval($_POST['product_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if (!in_array($action, ['approved', 'rejected']) || $productId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid']);
    exit();
}

$sql = "UPDATE products SET status = '$action' WHERE id = $productId";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'status' => $action]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Failed']);
}
?>
