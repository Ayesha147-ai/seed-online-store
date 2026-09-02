<?php

// Session
require_once '../includes/session.php';

// Database
require_once '../includes/db.php';

// Stripe configuration
require_once '../includes/config.php';

header('Content-Type: application/json');

// Frontend se data receive karo
$input = json_decode(file_get_contents('php://input'), true);

$amount = intval($input['amount'] ?? 0);

// Amount valid hai?
if ($amount <= 0) {
    echo json_encode([
        'error' => 'Invalid amount'
    ]);
    exit();
}

try {

    // Stripe Payment Intent create karo
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'usd',

        'metadata' => [
            'user_id' => getUserId() ?? 0
        ]
    ]);

    // Client ko secret bhejo
    echo json_encode([
        'clientSecret' => $paymentIntent->client_secret
    ]);

} catch (Exception $e) {

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>