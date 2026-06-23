<?php
// ============================================================
//   admin/approve-agent.php — Algorithm 6: ApproveAgent
//   Naya design: Approve hone par TABHI role 'agent' banta hai.
//   Reject hone par user normal 'farmer' hi rehta hai (block nahi hota).
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$agentUserId = intval($_POST['user_id'] ?? 0);
$action      = $_POST['action'] ?? ''; // 'approve' or 'reject'

if ($agentUserId <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'msg' => 'Invalid']);
    exit();
}

if ($action === 'approve') {
    // Role ko 'agent' mein upgrade karo (account active hi rehta hai)
    mysqli_query($conn, "UPDATE users SET role = 'agent', status = 'active' WHERE id = $agentUserId");
    mysqli_query($conn, "UPDATE agents SET is_approved = 1, approved_at = NOW() WHERE user_id = $agentUserId");
    echo json_encode(['success' => true, 'msg' => 'Agent approved']);
} else {
    // Reject — user normal farmer hi rehta hai, koi block nahi hota
    mysqli_query($conn, "UPDATE agents SET is_approved = 2 WHERE user_id = $agentUserId");
    echo json_encode(['success' => true, 'msg' => 'Agent application rejected']);
}
?>