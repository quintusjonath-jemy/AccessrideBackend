<?php

if (!function_exists('loadBackendEnv')) {
  function loadBackendEnv()
  {
    $envPath = __DIR__ . '/../../.env';
    if (!file_exists($envPath)) {
      return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      if (strpos(trim($line), '#') === 0) {
        continue;
      }

      if (strpos($line, '=') === false) {
        continue;
      }

      list($name, $value) = explode('=', $line, 2);
      $name = trim($name);
      $value = trim($value);

      // Strip quotes if they exist
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
}

class TwilioSMS
{
  private $sid;
  private $token;
  private $from;

  public function __construct($sid = null, $token = null, $from = null)
  {
    // Auto-load .env config
    loadBackendEnv();

    $this->sid = !empty($sid) ? $sid : (getenv('TWILIO_SID') ?: ($_ENV['TWILIO_SID'] ?? ''));
    $this->token = !empty($token) ? $token : (getenv('TWILIO_TOKEN') ?: ($_ENV['TWILIO_TOKEN'] ?? ''));
    $this->from = !empty($from) ? $from : (getenv('TWILIO_FROM') ?: ($_ENV['TWILIO_FROM'] ?? ''));
  }

  public function send($to, $message)
  {
    if (empty($this->sid) || empty($this->token) || empty($this->from) || empty($to)) {
      return false;
    }

    // Format phone number to E.164 if it's a Sri Lankan mobile (e.g. 0771234567 -> +94771234567)
    $to = trim($to);
    if (strpos($to, '+') !== 0) {
      if (strpos($to, '0') === 0) {
        $to = '+94' . substr($to, 1);
      } else {
        $to = '+94' . $to;
      }
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
    $postData = [
      'To' => $to,
      'From' => $this->from,
      'Body' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->sid}:{$this->token}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev compatibility

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
  }
}
?>
