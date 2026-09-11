<?php
// ============================================================
//   admin/pay-agent.php
//   Admin agent ko payment mark karta hai (Paid)
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

header('Content-Type: application/json');

$orderId = intval($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid order ID']);
    exit();
}

// Order exist karta hai?
$checkStmt = mysqli_prepare($conn, "SELECT id, payment_status, status FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($checkStmt, 'i', $orderId);
mysqli_stmt_execute($checkStmt);
$check = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

if (!$check) {
    echo json_encode(['success' => false, 'msg' => 'Order not found']);
    exit();
}

if ($check['status'] !== 'delivered') {
    echo json_encode(['success' => false, 'msg' => 'Order must be delivered before paying agent']);
    exit();
}

if ($check['payment_status'] === 'Paid') {
    echo json_encode(['success' => false, 'msg' => 'Agent already paid for this order']);
    exit();
}


// Order ka grand_total nikalo taake commission calculate ho sake
$totalStmt = mysqli_prepare($conn, "SELECT grand_total FROM orders WHERE id = ?");
mysqli_stmt_bind_param($totalStmt, 'i', $orderId);
mysqli_stmt_execute($totalStmt);
$orderRow = mysqli_fetch_assoc(mysqli_stmt_get_result($totalStmt));

$commission = round(floatval($orderRow['grand_total']) * 0.03, 2); // 3% admin commission

$updateStmt = mysqli_prepare($conn, "UPDATE orders SET payment_status = 'Paid', admin_commission = ? WHERE id = ?");
mysqli_stmt_bind_param($updateStmt, 'di', $commission, $orderId);

if (mysqli_stmt_execute($updateStmt)) {
    echo json_encode(['success' => true, 'msg' => 'Agent payment marked as Paid!']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
