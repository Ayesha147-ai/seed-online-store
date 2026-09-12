<?php
// ============================================================
//   payment/checkout.php — Stripe Checkout Session Integration Logic
//   Yeh file user ki session verification karti hai, session ya default amount se order ka grand total retrieve karti hai, Stripe Checkout Session create karke Pakistani Rupees (PKR) mein payment process karti hai, aur user ko Stripe ke hosted payment page par redirect karti hai
// ============================================================
session_start();

// Config file include kar rahe hain jahan database aur Stripe/configuration settings ho sakti hain.
require_once "../includes/config.php";

// 1. Check if the user is logged in
// Check kar rahe hain ke user session me logged in hai ya nahi.
if (!isset($_SESSION['user_id'])) {
    // Agar user login nahi hai to login page par redirect kar dete hain.
    header("Location: ../auth/login.php"); // Adjust if your login path is different
    exit();
}

// 2. Get the grand total from the session (or use a default test amount like 1000 PKR)
// Session se order ka grand total le rahe hain.
// Agar total session me nahi mila to testing ke liye 1000 PKR default use hoga.
$grand_total = isset($_SESSION['grand_total']) ? $_SESSION['grand_total'] : 1000;

try {
    // 3. Create a Stripe Checkout Session
    // Stripe ke liye checkout session create kar rahe hain.
    $checkout_session = \Stripe\Checkout\Session::create([
        // Payment ke liye card method allow kiya gaya hai.
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                // Stripe payment ki currency Pakistani Rupees set ki gayi hai.
                'currency' => 'pkr', // Pakistani Rupees
                'product_data' => [
                    // Checkout par product/order ka naam show hoga.
                    'name' => 'AgriService Order Payment',
                ],
                // Stripe amount ko smallest currency unit me expect karta hai.
                'unit_amount' => $grand_total * 100, // Stripe expects amount in paisa/cents (multiply by 100)
            ],
            // Checkout me quantity 1 rakhi gayi hai.
            'quantity' => 1,
        ]],
        // Payment mode one-time payment ke liye set hai.
        'mode' => 'payment',

        // Successful payment ke baad user is URL par redirect hoga.
        'success_url' => 'http://localhost/FYP/payment/payment_success.php?session_id={CHECKOUT_SESSION_ID}',

        // Payment cancel hone par user is URL par redirect hoga.
        'cancel_url' => 'http://localhost/FYP/payment/payment_cancel.php',
    ]);

    // 4. Redirect the user to the Stripe payment page
    // User ko Stripe ke hosted payment page par redirect kar rahe hain.
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();

} catch (Exception $e) {
    // Agar Stripe checkout create karte waqt error aaye to error message show hoga.
    echo "Error: " . $e->getMessage();
}
?>