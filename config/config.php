<?php
// Configuration file for StudyTrack

// Base path - root directory of the application
define('BASE_PATH', dirname(__DIR__));

// Load .env file if it exists (production)
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
    }
}

// Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Application Settings
define('APP_NAME', 'StudyTrack');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');

// Default timezone
$timezone = getenv('TIMEZONE') ?: 'Asia/Dhaka';
date_default_timezone_set($timezone);

// Error reporting
if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    $logDir = BASE_PATH . '/logs';
    if (!file_exists($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/php-errors.log');
} else {
    // Development mode
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session Configuration
define('SESSION_TIMEOUT', getenv('SESSION_LIFETIME') ?: 1800);

// Session security settings
ini_set('session.cookie_httponly', getenv('SESSION_COOKIE_HTTPONLY') ?: '1');
ini_set('session.cookie_secure', getenv('SESSION_COOKIE_SECURE') ?: '0'); // Set to 1 in production with HTTPS
ini_set('session.cookie_samesite', getenv('SESSION_COOKIE_SAMESITE') ?: 'Lax');
ini_set('session.use_strict_mode', '1');

// Start session
session_start();

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'student_id' => $_SESSION['student_id'] ?? null
        ];
    }
    return null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit;
    }
}

// Redirect if not teacher
function requireTeacher() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'teacher') {
        header('Location: /pages/calendar.php');
        exit;
    }
}
?>
