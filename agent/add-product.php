<?php
// ============================================================
//   agent/add-product.php — Seed Upload
//   Returns JSON (agent-dashboard.js isi ko expect karta hai)
// ============================================================

// Session, database aur helper functions ki required files load karo
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Check karo ke request karne wala user agent hai
requireAgent();
header('Content-Type: application/json');

// Check karo ke request POST method se aayi hai
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

// Form se seed ki tamam details receive karo
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
// Check karo ke required fields sahi values ke sath fill ki gayi hain
if (empty($name) || empty($catName) || $price <= 0 || $stock < 0) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields correctly.']);
    exit();
}

// Get category ID
// Selected category ke name se database mein uski ID hasil karo
$catStmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE name = ? LIMIT 1");
mysqli_stmt_bind_param($catStmt, 's', $catName);
mysqli_stmt_execute($catStmt);
$catResult = mysqli_stmt_get_result($catStmt);
$catRow    = mysqli_fetch_assoc($catResult);

// Check karo ke selected category database mein mojood hai
if (!$catRow) {
    echo json_encode(['success' => false, 'msg' => 'Invalid category selected.']);
    exit();
}
$categoryId = $catRow['id'];

// Handle image upload
// Seed ki image upload karne ke liye image path initialize karo
$imagePath = '';

// Check karo ke user ne seed ki image upload ki hai ya nahi
if (!empty($_FILES['seed-image']['name'])) {
    $uploadDir = '../css-f/img/products/';

    // Agar upload directory mojood nahi hai to usay create karo
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Uploaded image ka extension aur allowed image types define karo
    $ext     = strtolower(pathinfo($_FILES['seed-image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    // Check karo ke uploaded file asal mein image hai
    $isRealImage = @getimagesize($_FILES['seed-image']['tmp_name']) !== false;

    // Image type, size aur real image hone ki validation karo
    if (in_array($ext, $allowed) && $_FILES['seed-image']['size'] < 2000000 && $isRealImage) {

        // Image ke liye unique filename generate karo
        $filename  = 'seed_' . time() . '_' . $agentId . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['seed-image']['tmp_name'], $uploadDir . $filename);
        $imagePath = 'css-f/img/products/' . $filename;
    } else {
        // Agar image valid na ho to error response send karo
        echo json_encode(['success' => false, 'msg' => 'Invalid image file.']);
        exit();
    }
}

// Insert seed — status = 'pending' (admin approves)
// Seed ki tamam details database mein pending status ke sath save karo
$stmt = mysqli_prepare($conn, "INSERT INTO products
        (agent_id, category_id, name, seed_type, description, price, stock, quality, weight, season, image, status)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
mysqli_stmt_bind_param($stmt, 'iisssdiisss', $agentId, $categoryId, $name, $seedType, $description, $price, $stock, $quality, $weight, $season, $imagePath);

// Check karo ke seed successfully database mein add hua hai ya nahi
if (mysqli_stmt_execute($stmt)) {
    // Seed successfully submit hone ka response send karo
    echo json_encode(['success' => true, 'msg' => 'Seed submitted! Admin will review it shortly.']);
} else {
    // Agar seed add na ho to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Failed to add seed. Please try again.']);
}
exit();
?>