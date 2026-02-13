<?php
require_once __DIR__ . '/../config/config.php';

// Destroy session and redirect to login
session_unset();
session_destroy();
header('Location: /auth/login.php');
exit;
?>
