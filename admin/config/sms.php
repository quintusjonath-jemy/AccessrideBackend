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
    $gateway = getenv('SMS_GATEWAY') ?: 'twilio';

    // Format phone number to E.164 if it's a Sri Lankan mobile (e.g. 0771234567 -> +94771234567)
    $to = str_replace(' ', '', trim($to));
    if (strpos($to, '+') !== 0) {
      if (strpos($to, '0') === 0) {
        $to = '+94' . substr($to, 1);
      } else {
        $to = '+94' . $to;
      }
    }

    if (strtolower($gateway) === 'textbelt') {
      $url = "https://textbelt.com/text";
      $postData = [
        'phone' => $to,
        'message' => $message,
        'key' => 'textbelt'
      ];

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (getenv('CURL_SSL_VERIFY') !== 'false'));

      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($httpCode >= 200 && $httpCode < 300) {
        $resObj = json_decode($response, true);
        return isset($resObj['success']) && $resObj['success'] === true;
      }
      return false;
    }

    if (strtolower($gateway) === 'textlk') {
      // Format number to 947XXXXXXXX (strip + if present)
      $cleanTo = str_replace('+', '', $to);
      if (strpos($cleanTo, '0') === 0) {
        $cleanTo = '94' . substr($cleanTo, 1);
      }
      
      $url = "https://app.text.lk/api/v3/sms/send";
      $postData = [
        'recipient' => $cleanTo,
        'sender_id' => getenv('TEXTLK_SENDER_ID') ?: 'Promo',
        'message' => $message
      ];

      $apiKey = getenv('TEXTLK_API_KEY');

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
      ]);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (getenv('CURL_SSL_VERIFY') !== 'false'));

      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($httpCode >= 200 && $httpCode < 300) {
        $resObj = json_decode($response, true);
        return isset($resObj['status']) && ($resObj['status'] === 'success' || $resObj['status'] === true);
      }
      return false;
    }

    // Default: Twilio
    if (empty($this->sid) || empty($this->token) || empty($this->from) || empty($to)) {
      return false;
    }

    // Format From phone number to E.164 if it is a local number
    $from = str_replace(' ', '', trim($this->from));
    if (strpos($from, '+') !== 0 && is_numeric($from)) {
      if (strpos($from, '0') === 0) {
        $from = '+94' . substr($from, 1);
      } else {
        $from = '+94' . $from;
      }
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
    $postData = [
      'To' => $to,
      'From' => $from,
      'Body' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->sid}:{$this->token}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (getenv('CURL_SSL_VERIFY') !== 'false'));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
  }

  public function makeCall($to, $message)
  {
    if (empty($this->sid) || empty($this->token) || empty($this->from) || empty($to)) {
      return false;
    }

    // Format phone number to E.164
    $to = str_replace(' ', '', trim($to));
    if (strpos($to, '+') !== 0) {
      if (strpos($to, '0') === 0) {
        $to = '+94' . substr($to, 1);
      } else {
        $to = '+94' . $to;
      }
    }

    $from = str_replace(' ', '', trim($this->from));
    if (strpos($from, '+') !== 0 && is_numeric($from)) {
      if (strpos($from, '0') === 0) {
        $from = '+94' . substr($from, 1);
      } else {
        $from = '+94' . $from;
      }
    }

    // Wrap the message in TwiML
    $twiml = '<Response><Say voice="alice" loop="3">' . htmlspecialchars($message) . '</Say></Response>';

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Calls.json";
    $postData = [
      'To' => $to,
      'From' => $from,
      'Twiml' => $twiml
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->sid}:{$this->token}");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (getenv('CURL_SSL_VERIFY') !== 'false'));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
  }
}
?>
