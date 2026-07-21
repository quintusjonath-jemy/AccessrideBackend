<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load environment variables from the root .env file
function loadEnv() {
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath)) {
        return;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv();

// ──────────────────────────────────────────────────────────
// DB helper — reuses the same credentials as the rest of the backend
// ──────────────────────────────────────────────────────────
function getDB() {
    $host   = getenv('DB_HOST')     ?: 'localhost';
    $dbname = getenv('DB_NAME')     ?: 'accessride';
    $user   = getenv('DB_USER')     ?: 'root';
    $pass   = getenv('DB_PASSWORD') ?: '';

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        return null;
    }
    return $conn;
}

// ──────────────────────────────────────────────────────────
// Route by ?action= parameter
// ──────────────────────────────────────────────────────────
$action = $_GET['action'] ?? 'speak';

// ── ACTION: speak ─────────────────────────────────────────
// Proxy text → OpenAI TTS → return MP3 audio
// GET /voiceassistant/agent.php?action=speak&text=Hello
if ($action === 'speak') {
    $apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');

    if (empty($apiKey)) {
        http_response_code(400);
        echo json_encode([
            'error'   => 'OpenAI API key is missing.',
            'message' => 'Please add OPENAI_API_KEY=your_key to your backend .env file.'
        ]);
        exit;
    }

    if (empty($_GET['text'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parameter text is required.']);
        exit;
    }

    $text = trim($_GET['text']);

    $ch = curl_init('https://api.openai.com/v1/audio/speech');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model'           => 'tts-1',
        'input'           => $text,
        'voice'           => 'alloy',
        'response_format' => 'mp3'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $audio    = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $audio) {
        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . strlen($audio));
        echo $audio;
    } else {
        http_response_code(500);
        echo json_encode([
            'error'   => 'Failed to generate speech.',
            'details' => json_decode($audio, true) ?: $audio
        ]);
    }
    exit;
}

// ── ACTION: get_user_location ─────────────────────────────
// Returns the user's saved location from the users table (for "take me home")
// GET /voiceassistant/agent.php?action=get_user_location&user_id=5
if ($action === 'get_user_location') {
    header('Content-Type: application/json');

    $userId = (int)($_GET['user_id'] ?? 0);
    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'user_id is required.']);
        exit;
    }

    $db = getDB();
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
        exit;
    }

    $stmt = $db->prepare("SELECT location FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    if ($row && !empty($row['location'])) {
        echo json_encode(['success' => true, 'location' => $row['location']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No home location saved for this user.']);
    }
    exit;
}

// ── ACTION: get_last_ride ─────────────────────────────────
// Returns the user's most recent completed ride (for "same as last time")
// GET /voiceassistant/agent.php?action=get_last_ride&user_id=5
if ($action === 'get_last_ride') {
    header('Content-Type: application/json');

    $userId = (int)($_GET['user_id'] ?? 0);
    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'user_id is required.']);
        exit;
    }

    $db = getDB();
    if (!$db) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT dropoff_location, vehicle_type
        FROM rides
        WHERE user_id = ? AND status = 'completed'
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    if ($row && !empty($row['dropoff_location'])) {
        echo json_encode([
            'success'      => true,
            'destination'  => $row['dropoff_location'],
            'vehicle_type' => $row['vehicle_type'] ?? ''
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No previous completed ride found.']);
    }
    exit;
}

// ── Unknown action ────────────────────────────────────────
http_response_code(400);
header('Content-Type: application/json');
echo json_encode(['error' => 'Unknown action: ' . htmlspecialchars($action)]);
?>
