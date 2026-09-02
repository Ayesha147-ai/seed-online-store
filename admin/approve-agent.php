<?php
// ============================================================
//   admin/approve-agent.php — Algorithm 6: ApproveAgent
//   Naya design: Approve hone par TABHI role 'agent' banta hai.
//   Reject hone par user normal 'farmer' hi rehta hai (block nahi hota).
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$agentUserId = intval($_POST['user_id'] ?? 0);
$action      = $_POST['action'] ?? ''; // 'approve' or 'reject'

if ($agentUserId <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'msg' => 'Invalid']);
    exit();
}

if ($action === 'approve') {
    // Role ko 'agent' mein upgrade karo (account active hi rehta hai)
    $stmt1 = mysqli_prepare($conn, "UPDATE users SET role = 'agent', status = 'active' WHERE id = ?");
    mysqli_stmt_bind_param($stmt1, 'i', $agentUserId);
    mysqli_stmt_execute($stmt1);

    $stmt2 = mysqli_prepare($conn, "UPDATE agents SET is_approved = 1, approved_at = NOW() WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt2, 'i', $agentUserId);
    mysqli_stmt_execute($stmt2);

    echo json_encode(['success' => true, 'msg' => 'Agent approved']);
} else {
    // Reject — user normal farmer hi rehta hai, koi block nahi hota
    $stmt3 = mysqli_prepare($conn, "UPDATE agents SET is_approved = 2 WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt3, 'i', $agentUserId);
    mysqli_stmt_execute($stmt3);

    echo json_encode(['success' => true, 'msg' => 'Agent application rejected']);
}
?>