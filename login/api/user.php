<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/UserController.php';

$controller = new UserController();
$controller->getCurrentUser();
