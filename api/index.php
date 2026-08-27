<?php
// Vercel Serverless Entry Point Router for AccessRide PHP Backend

// Load .env file into getenv() and $_ENV if present
$envFile = realpath(__DIR__ . '/../.env');
if ($envFile && file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Universal CORS Handling for Vercel & Localhost
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-Token, Accept, Origin");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse request URI
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);

// Base directory (root of backend)
$baseDir = realpath(__DIR__ . '/..');
$targetFile = $baseDir . $uri;

// If URI is directory or root, check for index.php
if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

// If file exists and is PHP, execute it
if (file_exists($targetFile) && is_file($targetFile)) {
    $ext = pathinfo($targetFile, PATHINFO_EXTENSION);
    
    if ($ext === 'php') {
        chdir(dirname($targetFile));
        require $targetFile;
        exit;
    } else {
        // Serve static file (images, documents, etc.)
        $mime = mime_content_type($targetFile) ?: 'application/octet-stream';
        header("Content-Type: $mime");
        readfile($targetFile);
        exit;
    }
}

// Default 404
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'error',
    'message' => 'API endpoint not found: ' . $uri
]);
