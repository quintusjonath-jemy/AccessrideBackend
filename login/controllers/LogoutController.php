<?php
require_once __DIR__ . '/../config/config.php';

class LogoutController {
    public function logout() {
        session_unset();
        session_destroy();

        header('Location: ' . FRONTEND_BASE . '/admin-login');
        exit;
    }
}
