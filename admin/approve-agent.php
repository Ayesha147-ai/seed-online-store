<?php
// ============================================================
//   admin/approve-agent.php — Algorithm 6: ApproveAgent
//   Naya design: Approve hone par TABHI role 'agent' banta hai.
//   Reject hone par user normal 'farmer' hi rehta hai (block nahi hota).
// ============================================================
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');


// POST se agent user ki ID aur admin ka action receive karo
$agentUserId = intval($_POST['user_id'] ?? 0);
$action      = $_POST['action'] ?? ''; // 'approve' or 'reject'


// Check karo ke user ID valid hai aur action approve ya reject hai
if ($agentUserId <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'msg' => 'Invalid']);
    exit();
}


// Agar admin approve kare to user ka role agent mein change karo
if ($action === 'approve') {
   // User ka role agent aur account status active set karo
    $stmt1 = mysqli_prepare($conn, "UPDATE users SET role = 'agent', status = 'active' WHERE id = ?");
    mysqli_stmt_bind_param($stmt1, 'i', $agentUserId);
    mysqli_stmt_execute($stmt1);

    // Agent application ko approved mark karo aur approval ka time save karo
    $stmt2 = mysqli_prepare($conn, "UPDATE agents SET is_approved = 1, approved_at = NOW() WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt2, 'i', $agentUserId);
    mysqli_stmt_execute($stmt2);

    // Approval successful hone ka response admin ko send karo
    echo json_encode(['success' => true, 'msg' => 'Agent approved']);

    // Agar application reject ho to user ka farmer role same rehta hai
} else {
    // Agent application ko rejected status ke sath update karo
    $stmt3 = mysqli_prepare($conn, "UPDATE agents SET is_approved = 2 WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt3, 'i', $agentUserId);
    mysqli_stmt_execute($stmt3);

    // Rejection successful hone ka response admin ko send karo
    echo json_encode(['success' => true, 'msg' => 'Agent application rejected']);
}
?>