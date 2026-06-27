<?php

require_once '../config/config.php';
require_once '../controllers/AdminController.php';

$controller = new AdminController();
$controller->login();