<?php
// Simple router for PHP built-in server
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// If empty path, redirect to index.php
if (empty($path)) {
    include 'index.php';
    return true;
}

// Check if file exists directly (for auth/ and pages/ folders)
if (file_exists($path)) {
    if (preg_match('/\.php$/', $path)) {
        include $path;
        return true;
    }
    // Let PHP handle static files
    return false;
}

// Check if it's a PHP file without extension
$phpFile = $path . '.php';
if (file_exists($phpFile)) {
    include $phpFile;
    return true;
}

// Default to index.php
include 'index.php';
return true;
?>
