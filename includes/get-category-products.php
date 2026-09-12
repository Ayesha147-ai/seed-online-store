<?php
// ============================================================
//   includes/get-category-products.php
//   Public endpoint — koi login zaroori nahi
//   Category ke approved seeds deta hai (vegcat/frucat/herbcat ke liye)
// ============================================================

// Database connection ki required file load karo
require_once 'db.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// URL se category receive karo aur allowed categories define karo
$category = $_GET['category'] ?? '';
$allowed  = ['Vegetable', 'Fruit', 'Herb'];

// Check karo ke requested category allowed list mein hai ya nahi
if (!in_array($category, $allowed)) {
    echo json_encode([]);
    exit();
}

// Selected category ke approved products database se fetch karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "SELECT p.id, p.name, p.price, p.image, p.stock, p.seed_type, p.weight, p.season
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE c.name = ? AND p.status = 'approved'
        ORDER BY p.created_at DESC");

// Category ko query ke parameter ke saath bind karo
mysqli_stmt_bind_param($stmt, 's', $category);

// Query execute karo aur result hasil karo
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Products store karne ke liye empty array banao
$products = [];

// Har product ko products array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Products ka JSON response return karo
echo json_encode($products);
?>