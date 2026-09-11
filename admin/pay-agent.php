<?php
// ============================================================
//   admin/pay-agent.php
//   Admin agent ko payment mark karta hai (Paid)
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

header('Content-Type: application/json');

// POST se order ki ID receive karo
$orderId = intval($_POST['order_id'] ?? 0);

// Check karo ke order ID valid hai ya nahi
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid order ID']);
    exit();
}

// Order exist karta hai?
// Database mein check karo ke order mojood hai aur uski payment aur delivery status hasil karo
$checkStmt = mysqli_prepare($conn, "SELECT id, payment_status, status FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($checkStmt, 'i', $orderId);
mysqli_stmt_execute($checkStmt);
$check = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

// Check karo ke order database mein mojood hai ya nahi
if (!$check) {
    echo json_encode(['success' => false, 'msg' => 'Order not found']);
    exit();
}

// Check karo ke agent ko payment karne se pehle order delivered ho chuka hai
if ($check['status'] !== 'delivered') {
    echo json_encode(['success' => false, 'msg' => 'Order must be delivered before paying agent']);
    exit();
}

// Check karo ke is order ki agent payment pehle se Paid to nahi hai
if ($check['payment_status'] === 'Paid') {
    echo json_encode(['success' => false, 'msg' => 'Agent already paid for this order']);
    exit();
}


// Order ka grand_total nikalo taake commission calculate ho sake
// Order ka total amount hasil karo jiske basis par admin commission calculate hogi
$totalStmt = mysqli_prepare($conn, "SELECT grand_total FROM orders WHERE id = ?");
mysqli_stmt_bind_param($totalStmt, 'i', $orderId);
mysqli_stmt_execute($totalStmt);
$orderRow = mysqli_fetch_assoc(mysqli_stmt_get_result($totalStmt));

// Order ke total ka 3% admin commission calculate karo
$commission = round(floatval($orderRow['grand_total']) * 0.03, 2); // 3% admin commission

// Order ki payment status Paid aur admin commission database mein update karo
$updateStmt = mysqli_prepare($conn, "UPDATE orders SET payment_status = 'Paid', admin_commission = ? WHERE id = ?");
mysqli_stmt_bind_param($updateStmt, 'di', $commission, $orderId);

// Check karo ke payment status aur commission successfully update hue hain ya nahi
if (mysqli_stmt_execute($updateStmt)) {
    // Payment successfully Paid mark hone ka response send karo
    echo json_encode(['success' => true, 'msg' => 'Agent payment marked as Paid!']);
} else {
    // Agar update fail ho jaye to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}