<?php
// ============================================================
//   includes/process-contact.php
//   Contact form ka data save karta hai — ab login zaroori hai
// ============================================================

// Session, database aur helper functions ki required files load karo
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke request POST method se aa rahi hai
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

// Check karo ke user logged in hai ya nahi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Please login first to send a message.', 'need_login' => true]);
    exit();
}

// Current user ki ID aur contact form ki values hasil karo
$userId  = $_SESSION['user_id'];
$name    = clean($conn, $_POST['name']    ?? '');
$phone   = clean($conn, $_POST['phone']   ?? '');
$message = clean($conn, $_POST['message'] ?? '');

// Check karo ke tamam required fields fill ki gayi hain
if (empty($name) || empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields.']);
    exit();
}

// Contact message ko database mein save karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (user_id, name, phone, message) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'isss', $userId, $name, $phone, $message);

// Check karo ke message successfully database mein save hua hai ya nahi
if (mysqli_stmt_execute($stmt)) {
    // Successful submission ka response send karo
    echo json_encode(['success' => true, 'msg' => 'Message sent successfully!']);
} else {
    // Database error ko log karo aur failure response send karo
    error_log('Contact form insert failed: ' . mysqli_stmt_error($stmt));
    echo json_encode(['success' => false, 'msg' => 'Failed to send message. Please try again.']);
}
?>