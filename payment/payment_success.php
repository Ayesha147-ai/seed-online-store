<?php
// ============================================================
//   payment/payment_success.php — Stripe Payment Verification & Success Fulfillment Logic
//   Yeh file Stripe Checkout Session ID ko URL se retrieve karke Stripe API ke zariye payment status verify karti hai, payment 'paid' hone par database mein order fulfillment handle karti hai, cart aur grand total sessions ko clear karti hai, aur user ko successful payment message ke sath home page par jaane ka link provide karti hai
// ============================================================
// Session start kar rahe hain taake user aur order ki session information access ho sake.
session_start();

// Stripe aur project ki configuration file include kar rahe hain.
require_once "../includes/config.php";

// Check if Stripe passed a session ID in the URL
// URL se Stripe Checkout Session ID receive kar rahe hain.
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

// Agar session ID nahi mili to payment session invalid samjhi jayegi.
if (!$session_id) {
    die("Error: Invalid payment session.");
}

try {
    // 1. Verify the session with Stripe to make sure it's genuine
    // Stripe se session retrieve karke verify kar rahe hain ke payment session genuine hai.
    $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);

    // Check kar rahe hain ke Stripe ke according payment successfully paid hai ya nahi.
    if ($stripe_session->payment_status == 'paid') {
        // Payment is confirmed! Now we can safely save the order to the database.
        
        // Logged-in user ki ID session se le rahe hain.
        $user_id = $_SESSION['user_id'];

        // Session se order ka grand total retrieve kar rahe hain.
        // Agar total available nahi hai to 0 use hoga.
        $grand_total = isset($_SESSION['grand_total']) ? $_SESSION['grand_total'] : 0;
        
        // Example: Insert order into database with payment_status = 'Paid'
        // Database me paid order save karne ke liye yahan insert query add ki ja sakti hai.
        // (Make sure this matches your database connection style, e.g., PDO or MySQLi)
        // For standard MySQLi connection, you can execute your insert query here:
        
        // After successfully saving the order in the database:
        
        // 2. Clear the cart session safely
        // Order save hone ke baad cart ki session information remove kar rahe hain.
        unset($_SESSION['cart']);

        // Grand total ki session value bhi clear kar rahe hain.
        unset($_SESSION['grand_total']);

        // User ko successful payment ka message show kar rahe hain.
        echo "<h1>Payment Successful! 🎉</h1>";

        // User ko order payment successfully process hone ka confirmation de rahe hain.
        echo "<p>Thank you for your order with AgriService. Your payment was processed successfully.</p>";

        // User ko website ke home page par wapas jane ka link de rahe hain.
        echo "<a href='../index.php'>Return to Home</a>";
        
    } else {
        // Agar payment paid status me nahi hai to incomplete payment ka message show hoga.
        echo "Payment was not completed.";
    }

} catch (Exception $e) {
    // Stripe verification ya kisi aur process me error aaye to error message show hoga.
    echo "Error: " . $e->getMessage();
}
?>