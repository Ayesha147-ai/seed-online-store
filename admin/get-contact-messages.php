<?php
// ============================================================
//   admin/get-contact-messages.php
//   Saare contact form submissions deta hai
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// Database se tamam contact form messages latest order mein hasil karo
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$messages = [];

// Database se messages ko ek ek karke array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

// Messages ko JSON format mein convert karke response ke tor par send karo
echo json_encode($messages);
?>