<?php
// ============================================================
//   includes/config.php — Configuration & Environment Setup
//   Yeh file environment variables aur Stripe API keys ko configure karti hai
// ============================================================
// Composer ki autoload file load karo taake Stripe library available ho
require_once __DIR__ . '/../vendor/autoload.php';

// .env file ka path define karo
$env_path = __DIR__ . '/../.env';

// Check karo ke .env file exist karti hai ya nahi
if (!file_exists($env_path)) {
    die("Error: .env file nahi mili.");
}

// .env file ki configuration values read karo
$env = parse_ini_file($env_path);

// Check karo ke .env file successfully read hui hai ya nahi
if ($env === false) {
    die("Error: .env file read nahi ho rahi.");
}

// Check karo ke required Stripe keys .env file mein available hain
if (!isset($env['STRIPE_SECRET_KEY']) || !isset($env['STRIPE_PUBLISHABLE_KEY'])) {
    die("Error: Stripe keys .env file mein missing hain.");
}

// Stripe keys ko constants mein define karo
define('STRIPE_SECRET_KEY', $env['STRIPE_SECRET_KEY']);
define('STRIPE_PUBLISHABLE_KEY', $env['STRIPE_PUBLISHABLE_KEY']);

// Stripe API ko secret key ke saath configure karo
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);