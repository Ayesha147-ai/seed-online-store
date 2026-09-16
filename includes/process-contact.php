<?php
// ============================================================
//   includes/process-contact.php
//   Contact form ka message seedha Gmail par bhejta hai
//   (ab database mein save nahi hota — admin apne Gmail se
//   directly reply karega)
// ============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

// Form ki values hasil aur sanitize karo
$email   = clean($conn, trim($_POST['email']   ?? ''));
$message = clean($conn, trim($_POST['message'] ?? ''));

// Required fields check
if (empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields.']);
    exit();
}

// Email format valid hai ya nahi
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'msg' => 'Please enter a valid email address.']);
    exit();
}

// .env se Gmail credentials uthao (send-otp.php jaisa pattern)
$env = parse_ini_file(__DIR__ . '/../.env');
$gmailAddress  = $env['GMAIL_ADDRESS'] ?? '';
$gmailPassword = $env['GMAIL_APP_PASSWORD'] ?? '';


$destinationEmail = 'trackseedsupport+admin@gmail.com';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $gmailAddress;
    $mail->Password   = $gmailPassword;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Email TrackSeed ke Gmail account se jayegi
    $mail->setFrom($gmailAddress, 'TrackSeed Contact Form');

    // Reply-To user ka email set kiya — admin "Reply" dabayega to
    // seedha user ko jayega, TrackSeed ke Gmail ko nahi
    $mail->addReplyTo($email);

    // Destination — jahan message deliver hoga
    $mail->addAddress($destinationEmail);

    $mail->Subject = "New Contact Message from $email";
    $mail->Body    = "From: $email\n\nMessage:\n$message";

    $mail->send();

    echo json_encode(['success' => true, 'msg' => 'Message sent successfully!']);

} catch (Exception $e) {
    error_log('Contact email send failed: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'msg' => 'Failed to send message. Please try again.']);
}
?>