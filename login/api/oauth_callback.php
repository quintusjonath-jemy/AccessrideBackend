<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/OAuthController.php';

$controller = new OAuthController();
$controller->handleCallback();
