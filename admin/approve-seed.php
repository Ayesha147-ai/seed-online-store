<?php
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// POST se product ki ID aur admin ka action receive karo
$productId = intval($_POST['product_id'] ?? 0);
$action    = $_POST['action'] ?? '';

// Check karo ke action approved ya rejected hai aur product ID valid hai
if (!in_array($action, ['approved', 'rejected']) || $productId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid']);
    exit();
}

// Product ka status admin ke selected action ke mutabiq update karo
$stmt = mysqli_prepare($conn, "UPDATE products SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $action, $productId);

// Check karo ke product status successfully update hua hai ya nahi
if (mysqli_stmt_execute($stmt)) {
    // Product update successful hone ka response send karo
    echo json_encode(['success' => true, 'status' => $action]);
} else {
    // Agar update fail ho jaye to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Failed']);
}
?>