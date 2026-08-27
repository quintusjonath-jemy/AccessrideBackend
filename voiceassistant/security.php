<?php
/**
 * AccessRide Security Helper
 * Shared security utilities for all backend API endpoints.
 *
 * Usage:
 *   require_once __DIR__ . '/security.php';
 *   Security::corsHeaders();          // Lock CORS to allowed origins
 *   Security::requireSession();       // Abort if user not authenticated
 *   Security::checkOwnership($id);    // Abort if $id != session user
 *   Security::rateLimit('tts', 30);   // 30 calls/min max per IP
 *   $safe = Security::sanitizeText($raw, 500); // Trim & cap length
 */

class Security
{
    // ── Allowed frontend origins (dev + production) ───────────────────────────
    private static array $allowedOrigins = [
        'http://localhost:5173',   // Vite dev server
        'http://localhost:5174',   // Vite alternate port
        'http://localhost',        // Apache local
        'http://127.0.0.1',
        'http://127.0.0.1:5173',
    ];

    // ── CORS — only allow requests from known AccessRide frontends ────────────
    public static function corsHeaders(array $methods = ['GET', 'POST', 'OPTIONS']): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $envFrontend = getenv('FRONTEND_BASE') ?: ($_ENV['FRONTEND_BASE'] ?? '');
        $allowed = array_filter(array_merge([$envFrontend, 'https://accessride-frontend.vercel.app'], self::$allowedOrigins));

        if (!empty($origin) && (in_array($origin, $allowed, true) || str_ends_with($origin, '.vercel.app'))) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
        } elseif (!empty($origin)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
        } else {
            header('Access-Control-Allow-Origin: *');
        }

        $methodStr = implode(', ', $methods);
        header("Access-Control-Allow-Methods: {$methodStr}");
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Session-Token');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    // ── Session: verify the user is authenticated ─────────────────────────────
    // Returns the authenticated user_id or aborts with 401.
    public static function requireSession(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']['id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error'   => 'Unauthorized. Please log in again.'
            ]);
            exit;
        }

        return (int)$_SESSION['user']['id'];
    }

    // ── Ownership: ensure the requested user_id belongs to the session user ───
    // Prevents user A from reading user B's data.
    public static function checkOwnership(int $requestedUserId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionUserId = (int)($_SESSION['user']['id'] ?? 0);

        if ($sessionUserId === 0 || $sessionUserId !== $requestedUserId) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error'   => 'Forbidden. You do not have permission to access this resource.'
            ]);
            exit;
        }
    }

    // ── Rate Limiting (file-based, per IP per action) ─────────────────────────
    // $action    - unique key e.g. 'tts', 'get_location'
    // $maxPerMin - maximum calls per minute from one IP
    public static function rateLimit(string $action, int $maxPerMin = 30): void
    {
        $ip       = self::getClientIp();
        $safeIp   = preg_replace('/[^a-fA-F0-9:.]/', '_', $ip);
        $safeAct  = preg_replace('/[^a-zA-Z0-9_]/', '_', $action);
        $lockDir  = sys_get_temp_dir() . '/accessride_rl';

        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0700, true);
        }

        $lockFile = "{$lockDir}/{$safeIp}_{$safeAct}.json";
        $now      = time();
        $window   = 60; // 1 minute sliding window

        $data = ['timestamps' => []];

        if (file_exists($lockFile)) {
            $raw = file_get_contents($lockFile);
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        // Remove timestamps older than the window
        $data['timestamps'] = array_filter(
            $data['timestamps'],
            fn($ts) => ($now - $ts) < $window
        );

        if (count($data['timestamps']) >= $maxPerMin) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: 60');
            echo json_encode([
                'success' => false,
                'error'   => "Too many requests. Maximum {$maxPerMin} per minute. Please wait."
            ]);
            exit;
        }

        // Record this request
        $data['timestamps'][] = $now;
        file_put_contents($lockFile, json_encode($data), LOCK_EX);
    }

    // ── Sanitize & cap text input ─────────────────────────────────────────────
    public static function sanitizeText(string $text, int $maxLength = 500): string
    {
        // Strip HTML tags & trim whitespace
        $clean = trim(strip_tags($text));
        // Remove non-printable characters except common whitespace
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
        // Cap length
        if (mb_strlen($clean) > $maxLength) {
            $clean = mb_substr($clean, 0, $maxLength);
        }
        return $clean;
    }

    // ── Validate numeric user_id from GET or POST ─────────────────────────────
    public static function requireUserId(string $source = 'get'): int
    {
        $raw = ($source === 'post')
            ? ($_POST['user_id'] ?? null)
            : ($_GET['user_id']  ?? null);

        $userId = (int)$raw;
        if ($userId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'A valid user_id is required.']);
            exit;
        }
        return $userId;
    }

    // ── Get real client IP (handles proxies) ──────────────────────────────────
    private static function getClientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // X-Forwarded-For can be a comma-separated list
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }
}
?>
