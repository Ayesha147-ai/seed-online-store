<?php
session_start();
require_once "../includes/config.php";

// 1. Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); // Adjust if your login path is different
    exit();
}

// 2. Get the grand total from the session (or use a default test amount like 1000 PKR)
$grand_total = isset($_SESSION['grand_total']) ? $_SESSION['grand_total'] : 1000;

try {
    // 3. Create a Stripe Checkout Session
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'pkr', // Pakistani Rupees
                'product_data' => [
                    'name' => 'AgriService Order Payment',
                ],
                'unit_amount' => $grand_total * 100, // Stripe expects amount in paisa/cents (multiply by 100)
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/FYP%20Offline/payment/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/FYP%20Offline/payment/payment_cancel.php',
    ]);

    // 4. Redirect the user to the Stripe payment page
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>