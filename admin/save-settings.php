<?php
// ============================================================
//   admin/save-settings.php
//   Platform settings ko database mein save karta hai
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
requireAdmin();
header('Content-Type: application/json');

// POST se platform name, support email aur support phone receive karke clean karo
$platformName = clean($conn, $_POST['platform_name'] ?? '');
$supportEmail = clean($conn, $_POST['support_email'] ?? '');
$supportPhone = clean($conn, $_POST['support_phone'] ?? '');

// Check karo ke tamam required fields fill ki gayi hain
if (empty($platformName) || empty($supportEmail) || empty($supportPhone)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all fields.']);
    exit();
}

// Platform settings ko database mein update karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "UPDATE platform_settings
    SET platform_name = ?, support_email = ?, support_phone = ?
    WHERE id = 1");
mysqli_stmt_bind_param($stmt, 'sss', $platformName, $supportEmail, $supportPhone);

// Check karo ke settings successfully update hui hain ya nahi
if (mysqli_stmt_execute($stmt)) {
    // Settings successfully update hone ka response send karo
    echo json_encode(['success' => true, 'msg' => 'Settings updated!']);
} else {
    // Agar settings update na hon to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed. Please try again.']);
}
?>