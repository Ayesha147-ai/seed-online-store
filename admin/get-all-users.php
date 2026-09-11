<?php
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

// URL se user ka role receive karo
$role = $_GET['role'] ?? '';

// Agar role diya gaya ho to sirf us role ke users hasil karo
if ($role) {
    $stmt = mysqli_prepare($conn, "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at
        FROM users u
        WHERE u.role = ?
        ORDER BY u.created_at DESC");
    mysqli_stmt_bind_param($stmt, 's', $role);
} else {
    // Agar role na diya ho to admin ke ilawa tamam users hasil karo
    $stmt = mysqli_prepare($conn, "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at
        FROM users u
        WHERE u.role != 'admin'
        ORDER BY u.created_at DESC");
}

// Prepared query ko execute karo aur result hasil karo
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users  = [];

// Database se tamam users ko ek ek karke array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Users ko JSON format mein convert karke response ke tor par send karo
header('Content-Type: application/json');
echo json_encode($users);
?>