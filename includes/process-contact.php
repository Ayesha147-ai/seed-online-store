<?php
// ============================================================
//   includes/process-contact.php
//   Contact form ka data save karta hai — public endpoint
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

$name    = clean($conn, $_POST['name']    ?? '');
$phone   = clean($conn, $_POST['phone']   ?? '');
$message = clean($conn, $_POST['message'] ?? '');

if (empty($name) || empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields.']);
    exit();
}

$stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, phone, message) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sss', $name, $phone, $message);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'msg' => 'Message sent successfully!']);
} else {
    error_log('Contact form insert failed: ' . mysqli_stmt_error($stmt));
    echo json_encode(['success' => false, 'msg' => 'Failed to send message. Please try again.']);
}
?>