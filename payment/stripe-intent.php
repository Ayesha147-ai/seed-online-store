<?php
// ============================================================
//   payment/stripe-intent.php
//   Stripe Payment Intent banao
//   Stripe Guide se: Step 6 — Create Payment Intent
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';

// Stripe ka auto-loader (composer se install hoga)
// XAMPP pe: composer require stripe/stripe-php
// Ya manual: https://github.com/stripe/stripe-php/releases

// agar composer available hai
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
}

// Stripe Secret Key — apni key yahan rakho
// .env file use karna better hai production mein
$stripeSecretKey = 'sk_test_YOUR_SECRET_KEY_HERE';

header('Content-Type: application/json');

$input  = json_decode(file_get_contents('php://input'), true);
$amount = intval($input['amount'] ?? 0); // Amount in cents (Rs 480 → 480 * 100 = 48000)

if ($amount <= 0) {
    echo json_encode(['error' => 'Invalid amount']);
    exit();
}

try {
    \Stripe\Stripe::setApiKey($stripeSecretKey);

    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount'   => $amount,       // in cents
        'currency' => 'usd',         // Test mode mein USD use karo
        'metadata' => [
            'user_id' => getUserId() ?? 0,
        ]
    ]);

    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
