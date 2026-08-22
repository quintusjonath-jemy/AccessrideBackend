<?php
/**
 * AccessRide Voice Agent — Secured Endpoint
 *
 * Security layers applied:
 *  1. CORS locked to known AccessRide origins only
 *  2. PHP session authentication required on sensitive actions
 *  3. User ownership check (can only read own data)
 *  4. Rate limiting per IP (30 req/min TTS, 60 req/min others)
 *  5. Input sanitization + text length cap (500 chars for TTS)
 *  6. No raw errors exposed to client in production
 */

require_once __DIR__ . '/security.php';

// ── Load environment variables from the root .env file ────────────────────────
function loadEnv(): void
{
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath)) return;

    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        if (preg_match('/^"(.*)"$/s', $value, $m) || preg_match("/^'(.*)'$/s", $value, $m)) {
            $value = $m[1];
        }
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $_SERVER[$name] = $value;
        }
    }
}

loadEnv();

// ─────────────────────────────────────────────────────────────────────────────
// SECURITY LAYER 1 — CORS lockdown (must run before any output)
// ─────────────────────────────────────────────────────────────────────────────
Security::corsHeaders(['GET', 'POST', 'OPTIONS']);

// ── DB helper ────────────────────────────────────────────────────────────────
function getDB(): ?mysqli
{
    $host   = getenv('DB_HOST')     ?: 'localhost';
    $dbname = getenv('DB_NAME')     ?: 'accessride';
    $user   = getenv('DB_USER')     ?: 'root';
    $pass   = getenv('DB_PASSWORD') ?: '';

    $conn = new mysqli($host, $user, $pass, $dbname);
    return $conn->connect_error ? null : $conn;
}

// ── Route by ?action= ─────────────────────────────────────────────────────────
$action = $_GET['action'] ?? 'speak';

// =============================================================================
// ACTION: speak — TTS proxy → OpenAI
// SECURITY: rate-limited (30 calls/min per IP), text sanitized & capped
// No session required — TTS itself carries no private data.
// =============================================================================
if ($action === 'speak') {

    // SECURITY LAYER 4 — Rate limit TTS (most expensive action)
    Security::rateLimit('tts', 30);

    $apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');
    if (empty($apiKey)) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Voice service is not configured.']);
        exit;
    }

    if (empty($_GET['text'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Parameter text is required.']);
        exit;
    }

    // SECURITY LAYER 5 — Sanitize and cap text length at 500 characters
    $text = Security::sanitizeText($_GET['text'], 500);

    if (empty($text)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Text is empty after sanitization.']);
        exit;
    }

    $ch = curl_init('https://api.openai.com/v1/audio/speech');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER    => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POST          => true,
        CURLOPT_POSTFIELDS    => json_encode([
            'model'           => 'tts-1',
            'input'           => $text,
            'voice'           => 'alloy',
            'response_format' => 'mp3',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $audio    = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $audio) {
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . strlen($audio));
        header('Cache-Control: no-store');
        echo $audio;
    } else {
        http_response_code(502);
        header('Content-Type: application/json');
        // Never expose raw OpenAI error to the client in production
        echo json_encode(['error' => 'Failed to generate speech. Please try again.']);
    }
    exit;
}

// =============================================================================
// ACTION: get_user_location — fetch user's saved home location from DB
// SECURITY: session required + user ownership check + rate limited
// =============================================================================
if ($action === 'get_user_location') {
    header('Content-Type: application/json');

    // SECURITY LAYER 2 — Must be logged in
    $sessionUserId = Security::requireSession();

    // SECURITY LAYER 4 — Rate limit location lookups
    Security::rateLimit('get_location', 60);

    // SECURITY LAYER 3 — user_id in request must match session
    $requestedId = (int)($_GET['user_id'] ?? 0);
    Security::checkOwnership($requestedId);

    $db = getDB();
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database unavailable.']);
        exit;
    }

    $stmt = $db->prepare("SELECT location FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $requestedId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    if ($row && !empty($row['location'])) {
        echo json_encode(['success' => true, 'location' => $row['location']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No home location saved.']);
    }
    exit;
}

// =============================================================================
// ACTION: get_last_ride — fetch last completed ride for "same as last time"
// SECURITY: session required + user ownership check + rate limited
// =============================================================================
if ($action === 'get_last_ride') {
    header('Content-Type: application/json');

    // SECURITY LAYER 2 — Must be logged in
    $sessionUserId = Security::requireSession();

    // SECURITY LAYER 4 — Rate limit
    Security::rateLimit('get_last_ride', 60);

    // SECURITY LAYER 3 — Ownership check
    $requestedId = (int)($_GET['user_id'] ?? 0);
    Security::checkOwnership($requestedId);

    $db = getDB();
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database unavailable.']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT dropoff_location, vehicle_type
        FROM rides
        WHERE user_id = ? AND status = 'completed'
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->bind_param('i', $requestedId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    if ($row && !empty($row['dropoff_location'])) {
        echo json_encode([
            'success'      => true,
            'destination'  => $row['dropoff_location'],
            'vehicle_type' => $row['vehicle_type'] ?? '',
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No previous completed ride found.']);
    }
    exit;
}

// =============================================================================
// Unknown action — return generic error (do not expose internals)
// =============================================================================
http_response_code(400);
header('Content-Type: application/json');
echo json_encode(['error' => 'Unknown action.']);
?>
