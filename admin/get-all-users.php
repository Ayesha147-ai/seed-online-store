<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$role = $_GET['role'] ?? '';
$where = $role ? "WHERE u.role = '" . mysqli_real_escape_string($conn, $role) . "'" : "WHERE u.role != 'admin'";

$sql = "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at
        FROM users u
        $where
        ORDER BY u.created_at DESC";

$result = mysqli_query($conn, $sql);
$users  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
