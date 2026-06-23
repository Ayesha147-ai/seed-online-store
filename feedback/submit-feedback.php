<?php
// ============================================================
//   feedback/submit-feedback.php
//   Sirf logged-in farmer jo order kar chuka ho feedback de sakta hai
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
$orderCheck = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT id FROM orders WHERE id = $orderId AND user_id = $userId AND status = 'delivered' LIMIT 1")
);
if (!$orderCheck) {
    echo json_encode(['success' => false, 'msg' => 'You can only review delivered orders']);
    exit();
}

// Check: already reviewed this product for this order?
$existing = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT id FROM feedback WHERE user_id = $userId AND order_id = $orderId AND product_id = $productId LIMIT 1")
);
if ($existing) {
    echo json_encode(['success' => false, 'msg' => 'You already reviewed this product']);
    exit();
}

// Save feedback
$sql = "INSERT INTO feedback (user_id, product_id, order_id, rating, comment)
        VALUES ($userId, $productId, $orderId, $rating, '$comment')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'msg' => 'Thank you for your feedback!']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Failed to save feedback']);
}
?>
