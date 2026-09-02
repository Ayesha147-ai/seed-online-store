<?php
// ============================================================
//   includes/get-category-products.php
//   Public endpoint — koi login zaroori nahi
//   Category ke approved seeds deta hai (vegcat/frucat/herbcat ke liye)
// ============================================================
require_once 'db.php';
header('Content-Type: application/json');

$category = $_GET['category'] ?? '';
$allowed  = ['Vegetable', 'Fruit', 'Herb'];

if (!in_array($category, $allowed)) {
    echo json_encode([]);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT p.id, p.name, p.price, p.image, p.stock, p.seed_type, p.weight, p.season
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE c.name = ? AND p.status = 'approved'
        ORDER BY p.created_at DESC");
mysqli_stmt_bind_param($stmt, 's', $category);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
?>