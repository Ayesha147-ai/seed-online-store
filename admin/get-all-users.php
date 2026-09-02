<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$role = $_GET['role'] ?? '';

if ($role) {
    $stmt = mysqli_prepare($conn, "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at
        FROM users u
        WHERE u.role = ?
        ORDER BY u.created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $role);
} else {
    $stmt = mysqli_prepare($conn, "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at
        FROM users u
        WHERE u.role != 'admin'
        ORDER BY u.created_at DESC");
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
