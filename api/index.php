<?php
// Vercel Serverless Entry Point Router for AccessRide PHP Backend

// Handle CORS Headers
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
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
