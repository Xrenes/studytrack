<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Otherwise redirect to calendar (home page)
header('Location: /pages/calendar.php');
exit;
?>
