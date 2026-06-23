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

$catSafe = mysqli_real_escape_string($conn, $category);

$sql = "SELECT p.id, p.name, p.price, p.image, p.stock, p.seed_type, p.weight, p.season
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE c.name = '$catSafe' AND p.status = 'approved'
        ORDER BY p.created_at DESC";

$result   = mysqli_query($conn, $sql);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
?>