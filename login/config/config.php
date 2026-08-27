<?php
// Minimal config for Google OAuth backend
session_start();

// Allowed frontend origins (dynamically supports FRONTEND_BASE env and vercel domains)
$envFrontend = getenv('FRONTEND_BASE') ?: ($_ENV['FRONTEND_BASE'] ?? '');
$allowedOrigins = array_filter([
    $envFrontend,
    'https://accessride-frontend.vercel.app',
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost',
    'http://127.0.0.1',
    'http://127.0.0.1:5173',
]);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin) && (in_array($origin, $allowedOrigins, true) || str_ends_with($origin, '.vercel.app'))) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
} elseif (!empty($origin)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Read from environment or replace with literal values for testing

if (!defined('BACKEND_BASE')) {
    define('BACKEND_BASE', getenv('BACKEND_BASE') ?: 'http://localhost');
}

if (!defined('FRONTEND_BASE')) {
    define('FRONTEND_BASE', getenv('FRONTEND_BASE') ?: 'http://localhost:5173/');
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'accessride');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
}

header('Content-Type: application/json; charset=utf-8');
