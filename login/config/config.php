<?php
// Minimal config for Google OAuth backend
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Read from environment or replace with literal values for testing

if (!defined('BACKEND_BASE')) {
    define('BACKEND_BASE', getenv('BACKEND_BASE') ?: 'http://localhost');
}

if (!defined('FRONTEND_BASE')) {
    define('FRONTEND_BASE', getenv('FRONTEND_BASE') ?: 'http://localhost:5173/');
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
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
