<?php
// ============================================================
//   feedback/submit-feedback.php
//   Sirf logged-in user jo order kar chuka ho feedback de sakta hai
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]); exit();
}

$userId    = getUserId();
$productId = intval($_POST['product_id'] ?? 0);
$orderId   = intval($_POST['order_id']   ?? 0);
$rating    = intval($_POST['rating']     ?? 0);
$comment   = clean($conn, $_POST['comment'] ?? '');

// Validation
if ($productId <= 0 || $orderId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    exit();
}

// Check: order belongs to this user and is delivered
// Order isi user ka hai aur delivered hai, yeh check karne ke liye query prepare karo
$orderStmt = mysqli_prepare($conn, "SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'delivered' LIMIT 1");
// Order ID aur user ID ko query ke parameters ke saath bind karo
mysqli_stmt_bind_param($orderStmt, 'ii', $orderId, $userId);
mysqli_stmt_execute($orderStmt);
$orderCheck = mysqli_fetch_assoc(mysqli_stmt_get_result($orderStmt));

if (!$orderCheck) {
    echo json_encode(['success' => false, 'msg' => 'You can only review delivered orders']);
    exit();
}

// Check: already reviewed this product for this order?
// Is order ke is product ka feedback pehle se maujood hai ya nahi, yeh check karne ke liye query prepare karo
$existingStmt = mysqli_prepare($conn, "SELECT id FROM feedback WHERE user_id = ? AND order_id = ? AND product_id = ? LIMIT 1");
// User ID, order ID aur product ID ko query ke parameters ke saath bind karo
mysqli_stmt_bind_param($existingStmt, 'iii', $userId, $orderId, $productId);
mysqli_stmt_execute($existingStmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($existingStmt));

if ($existing) {
    echo json_encode(['success' => false, 'msg' => 'You already reviewed this product']);
    exit();
}

// Save feedback
// Feedback ko database mein save karne ke liye query prepare karo
$insertStmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, product_id, order_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)");
// Feedback ki tamam values (comment sameet) ko query ke parameters ke saath bind karo
mysqli_stmt_bind_param($insertStmt, 'iiiis', $userId, $productId, $orderId, $rating, $comment);

// Check karo ke feedback successfully database mein save hua hai ya nahi
if (mysqli_stmt_execute($insertStmt)) {
    echo json_encode(['success' => true, 'msg' => 'Thank you for your feedback!']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Failed to save feedback']);
}
?>