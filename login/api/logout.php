<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/LogoutController.php';

$controller = new LogoutController();
$controller->logout();
