<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
        $name = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match('/^\'(.*)\'$/', $value, $matches)) {
            $value = $matches[1];
        }
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv();

$apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');

if (empty($apiKey)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'OpenAI API key is missing.',
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

// Call OpenAI TTS API (tts-1 model with alloy voice)
$ch = curl_init('https://api.openai.com/v1/audio/speech');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'tts-1',
    'input' => $text,
    'voice' => 'alloy',
    'response_format' => 'mp3'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$audio = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $audio) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($audio));
    echo $audio;
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to generate speech.',
        'details' => json_decode($audio, true) ?: $audio
    ]);
}
?>
