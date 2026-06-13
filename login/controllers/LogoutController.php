<?php
require_once __DIR__ . '/../config/config.php';

class LogoutController {
    public function logout() {
        session_unset();
        session_destroy();

        $redirectBase = defined('FRONTEND_BASE') ? rtrim(FRONTEND_BASE, '/') : '';
        header('Location: ' . $redirectBase . '/admin-login');
        exit;
    }
}
