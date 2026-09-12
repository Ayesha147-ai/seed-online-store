<?php
// ============================================================
//   includes/get-product-detail.php
//   Public endpoint — single approved product ki full detail
// ============================================================

// Database connection ki required file load karo
require_once 'db.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// URL se product ki ID receive karo
$id = intval($_GET['id'] ?? 0);

// Check karo ke product ID valid hai ya nahi
if ($id <= 0) {
    echo json_encode(null);
    exit();
}

// Approved product ki complete details aur category name fetch karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status = 'approved'
        LIMIT 1");

// Product ID ko query ke parameter ke saath bind karo
mysqli_stmt_bind_param($stmt, 'i', $id);

// Query execute karo aur product ka result hasil karo
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Product ki details JSON response mein return karo, aur na milne par null return karo
echo json_encode($product ?: null);
?>