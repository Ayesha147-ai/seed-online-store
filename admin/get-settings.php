<?php
// ============================================================
//   admin/get-settings.php
//   Platform settings wapas deta hai (Platform Name, Support Email/Phone)
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// Database se platform ki settings hasil karo
$result   = mysqli_query($conn, "SELECT platform_name, support_email, support_phone FROM platform_settings WHERE id = 1 LIMIT 1");
$settings = mysqli_fetch_assoc($result);

// Agar settings ki row na mile to default values use karo
if (!$settings) {
    // Agar kisi wajah se row hi na mile, to safe defaults bhej do
    $settings = [
        'platform_name' => 'TrackSeed',
        'support_email' => 'support@trackseed.pk',
        'support_phone' => ''
    ];
}

// Platform settings ko JSON format mein convert karke response ke tor par send karo
echo json_encode($settings);
?>