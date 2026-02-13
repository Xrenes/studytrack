<?php
/**
 * Health Check Endpoint
 * 
 * Use this endpoint to monitor your application status.
 * Compatible with monitoring services like UptimeRobot, Pingdom, etc.
 * 
 * URL: https://yourdomain.com/health.php
 * 
 * Returns JSON with status and checks:
 * - HTTP 200: All healthy
 * - HTTP 503: Service unavailable (database issues)
 */

header('Content-Type: application/json');

$status = 'healthy';
$httpCode = 200;
$checks = [];
$errors = [];

// Check 1: PHP Version
$checks['php_version'] = PHP_VERSION;
$checks['php_version_ok'] = version_compare(PHP_VERSION, '7.4.0', '>=');

if (!$checks['php_version_ok']) {
    $status = 'unhealthy';
    $httpCode = 503;
    $errors[] = 'PHP version too old (7.4+ required)';
}

// Check 2: Required Extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
$checks['extensions'] = [];

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $checks['extensions'][$ext] = $loaded;
    
    if (!$loaded) {
        $status = 'unhealthy';
        $httpCode = 503;
        $errors[] = "Missing extension: {$ext}";
    }
}

// Check 3: Database Connection
try {
    require_once __DIR__ . '/config/Database.php';
    $pdo = getDbConnection();
    $checks['database'] = 'connected';
} catch (Exception $e) {
    $status = 'unhealthy';
    $httpCode = 503;
    $checks['database'] = 'failed';
    $errors[] = 'Database connection failed';
}

// Check 4: Database Tables
if (isset($pdo)) {
    try {
        $requiredTables = ['users', 'sections', 'section_members', 'events'];
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $checks['tables'] = [];
        foreach ($requiredTables as $table) {
            $exists = in_array($table, $existingTables);
            $checks['tables'][$table] = $exists;
            
            if (!$exists) {
                $status = 'degraded';
                if ($httpCode === 200) $httpCode = 503;
                $errors[] = "Missing table: {$table}";
            }
        }
        
        // Count records
        if ($status !== 'unhealthy') {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $checks['user_count'] = (int)$stmt->fetchColumn();
            
            if ($checks['user_count'] === 0) {
                $status = 'degraded';
                $errors[] = 'No users in database (migration may not have run)';
            }
        }
        
    } catch (Exception $e) {
        $status = 'degraded';
        $checks['tables'] = 'error';
        $errors[] = 'Could not verify tables';
    }
}

// Check 5: Session
session_start();
$checks['session'] = session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive';

// Check 6: File Permissions
$checks['writable'] = [];
$writablePaths = [
    'logs' => __DIR__ . '/logs',
];

foreach ($writablePaths as $name => $path) {
    if (file_exists($path)) {
        $checks['writable'][$name] = is_writable($path);
        if (!$checks['writable'][$name]) {
            $status = 'degraded';
            $errors[] = "{$name} directory not writable";
        }
    } else {
        $checks['writable'][$name] = 'missing';
    }
}

// Check 7: Configuration
$checks['config'] = [];
$checks['config']['env_file'] = file_exists(__DIR__ . '/.env');
$checks['config']['database_config'] = file_exists(__DIR__ . '/config/Database.php');
$checks['config']['htaccess'] = file_exists(__DIR__ . '/.htaccess');

// Build response
$response = [
    'status' => $status,
    'timestamp' => date('c'),
    'uptime' => function_exists('uptime') ? uptime() : 'unknown',
    'checks' => $checks,
];

// Add errors if any
if (!empty($errors)) {
    $response['errors'] = $errors;
}

// Add memory usage
$response['memory'] = [
    'usage' => memory_get_usage(true),
    'usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
    'peak' => memory_get_peak_usage(true),
    'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
];

// Set HTTP response code
http_response_code($httpCode);

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
