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

$sql = "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = $id AND p.status = 'approved'
        LIMIT 1";

$result  = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

echo json_encode($product ?: null);
?>