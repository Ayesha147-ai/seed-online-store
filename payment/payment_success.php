<?php
session_start();
require_once "../includes/config.php";

// Check if Stripe passed a session ID in the URL
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

if (!$session_id) {
    die("Error: Invalid payment session.");
}

try {
    // 1. Verify the session with Stripe to make sure it's genuine
    $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);

    if ($stripe_session->payment_status == 'paid') {
        // Payment is confirmed! Now we can safely save the order to the database.
        
        $user_id = $_SESSION['user_id'];
        $grand_total = isset($_SESSION['grand_total']) ? $_SESSION['grand_total'] : 0;
        
        // Example: Insert order into database with payment_status = 'Paid'
        // (Make sure this matches your database connection style, e.g., PDO or MySQLi)
        // For standard MySQLi connection, you can execute your insert query here:
        
        // After successfully saving the order in the database:
        
        // 2. Clear the cart session safely
        unset($_SESSION['cart']);
        unset($_SESSION['grand_total']);

        echo "<h1>Payment Successful! 🎉</h1>";
        echo "<p>Thank you for your order with AgriService. Your payment was processed successfully.</p>";
        echo "<a href='../index.php'>Return to Home</a>";
        
    } else {
        echo "Payment was not completed.";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>