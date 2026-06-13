<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class OAuthController {
    private $tokenEndpoint = 'https://oauth2.googleapis.com/token';
    private $userinfoEndpoint = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function handleCallback() {
        if (!isset($_GET['code'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing authorization code']);
            exit;
        }

        $code = $_GET['code'];
        $tokenData = $this->exchangeCodeForToken($code);

        if (!isset($tokenData['access_token'])) {
            http_response_code(500);
            echo json_encode(['error' => 'No access_token in token response', 'response' => $tokenData]);
            exit;
        }

        $accessToken = $tokenData['access_token'];
        $userInfo = $this->fetchUserInfo($accessToken);

        if (method_exists(User::class, 'fromGoogleProfile')) {
            $_SESSION['user'] = User::fromGoogleProfile($userInfo);
        } else {
            $_SESSION['user'] = (object)[
                'id' => $userInfo['sub'] ?? null,
                'email' => $userInfo['email'] ?? null,
                'name' => $userInfo['name'] ?? null,
                'picture' => $userInfo['picture'] ?? null,
                'verified_email' => $userInfo['email_verified'] ?? null,
            ];
        }

        header('Location: ' . FRONTEND_BASE . '/admin-login?auth=success');
        exit;
    }

    private function exchangeCodeForToken(string $code) {
        $postFields = http_build_query([
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ]);

        $ch = curl_init($this->tokenEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        if ($response === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Token request failed', 'details' => curl_error($ch)]);
            exit;
        }

        curl_close($ch);
        return json_decode($response, true);
    }

    private function fetchUserInfo(string $accessToken) {
        $ch = curl_init($this->userinfoEndpoint . '?alt=json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$accessToken}"]);

        $response = curl_exec($ch);
        if ($response === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Userinfo request failed', 'details' => curl_error($ch)]);
            exit;
        }

        curl_close($ch);
        return json_decode($response, true);
    }
}
