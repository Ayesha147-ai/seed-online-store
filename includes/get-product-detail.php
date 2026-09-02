<?php
// ============================================================
//   includes/get-product-detail.php
//   Public endpoint — single approved product ki full detail
// ============================================================
require_once 'db.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(null);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status = 'approved'
        LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

echo json_encode($product ?: null);
?>