<?php
// ============================================================
//   agent/add-product.php — Seed Upload
//   Returns JSON (agent-dashboard.js isi ko expect karta hai)
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireAgent();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

$agentId     = getUserId();
$name        = clean($conn, $_POST['seed-name']     ?? '');
$catName     = clean($conn, $_POST['seed-category'] ?? '');
$seedType    = clean($conn, $_POST['seed-type']     ?? '');
$price       = floatval($_POST['seed-price']  ?? 0);
$stock       = intval($_POST['seed-stock']    ?? 0);
$quality     = clean($conn, $_POST['seed-quality']  ?? '');
$description = clean($conn, $_POST['seed-desc']     ?? '');
$weight      = clean($conn, $_POST['seed-weight']   ?? '');
$season      = clean($conn, $_POST['seed-season']   ?? '');

// Validation
if (empty($name) || empty($catName) || $price <= 0 || $stock < 0) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields correctly.']);
    exit();
}

// Get category ID
$catStmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE name = ? LIMIT 1");
mysqli_stmt_bind_param($catStmt, 's', $catName);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);
$catRow    = mysqli_fetch_assoc($catResult);
if (!$catRow) {
    echo json_encode(['success' => false, 'msg' => 'Invalid category selected.']);
    exit();
}
$categoryId = $catRow['id'];

// Handle image upload
$imagePath = '';
if (!empty($_FILES['seed-image']['name'])) {
    $uploadDir = '../css-f/img/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext     = strtolower(pathinfo($_FILES['seed-image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    $isRealImage = @getimagesize($_FILES['seed-image']['tmp_name']) !== false;

    if (in_array($ext, $allowed) && $_FILES['seed-image']['size'] < 2000000 && $isRealImage) {
        $filename  = 'seed_' . time() . '_' . $agentId . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['seed-image']['tmp_name'], $uploadDir . $filename);
        $imagePath = 'css-f/img/products/' . $filename;
    } else {
        echo json_encode(['success' => false, 'msg' => 'Invalid image file.']);
        exit();
    }
}

// Insert seed — status = 'pending' (admin approves)
$stmt = mysqli_prepare($conn, "INSERT INTO products
        (agent_id, category_id, name, seed_type, description, price, stock, quality, weight, season, image, status)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
mysqli_stmt_bind_param($stmt, 'iisssdiisss', $agentId, $categoryId, $name, $seedType, $description, $price, $stock, $quality, $weight, $season, $imagePath);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'msg' => 'Seed submitted! Admin will review it shortly.']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Failed to add seed. Please try again.']);
}
exit();
?>