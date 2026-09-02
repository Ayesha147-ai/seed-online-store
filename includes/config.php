<?php

require_once __DIR__ . '/../vendor/autoload.php';

$env_path = __DIR__ . '/../.env';

if (!file_exists($env_path)) {
    die("Error: .env file nahi mili.");
}

$env = parse_ini_file($env_path);

if ($env === false) {
    die("Error: .env file read nahi ho rahi.");
}

if (!isset($env['STRIPE_SECRET_KEY']) || !isset($env['STRIPE_PUBLISHABLE_KEY'])) {
    die("Error: Stripe keys .env file mein missing hain.");
}

define('STRIPE_SECRET_KEY', $env['STRIPE_SECRET_KEY']);
define('STRIPE_PUBLISHABLE_KEY', $env['STRIPE_PUBLISHABLE_KEY']);

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);