<?php
// ============================================================
//   payment/stripe-intent.php — Stripe Payment Intent Creation & Client Secret Generation Logic
//   Yeh file session, database, aur configuration load karke frontend se incoming JSON data se amount retrieve aur validate karti hai, Stripe Payment Intent (USD currency aur user ID metadata ke sath) create karti hai, aur frontend ko secure transaction complete karne ke liye client secret return karti hai
// ============================================================
// Session
// User ki session information aur login-related functions load kar rahe hain.
require_once '../includes/session.php';

// Database
// Database connection aur related functions include kar rahe hain.
require_once '../includes/db.php';

// Stripe configuration
// Stripe ki configuration aur API settings load kar rahe hain.
require_once '../includes/config.php';

// Response ko JSON format me set kar rahe hain.
header('Content-Type: application/json');

// Frontend se data receive karo
// Frontend se JSON data read karke PHP array me convert kar rahe hain.
$input = json_decode(file_get_contents('php://input'), true);

// Frontend se amount le rahe hain aur integer me convert kar rahe hain.
$amount = intval($input['amount'] ?? 0);

// Amount valid hai?
// Check kar rahe hain ke amount zero ya negative to nahi hai.
if ($amount <= 0) {
    // Invalid amount ki error JSON response me bhej rahe hain.
    echo json_encode([
        'error' => 'Invalid amount'
    ]);
    exit();
}

try {

    // Stripe Payment Intent create karo
    // Stripe par payment intent create kar rahe hain.
    $paymentIntent = \Stripe\PaymentIntent::create([
        // Payment ki amount Stripe ko send kar rahe hain.
        'amount' => $amount,

        // Payment ki currency USD set ki gayi hai.
        'currency' => 'usd',

        // Payment ke sath user ki ID metadata me save kar rahe hain.
        'metadata' => [
            'user_id' => getUserId() ?? 0
        ]
    ]);

    // Client ko secret bhejo
    // Frontend ko Payment Intent ka client secret bhej rahe hain taake payment complete ho sake.
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret
    ]);

} catch (Exception $e) {

    // Agar Stripe payment intent create karte waqt error aaye to error JSON response bhej rahe hain.
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>