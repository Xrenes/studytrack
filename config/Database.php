<?php
/**
 * Database Connection Configuration
 * PDO connection to PlanetScale MySQL database
 */

// Database configuration
// Use environment variables for production, fallback to local for development
$db_config = [
    'host' => getenv('DATABASE_HOST') ?: 'localhost',
    'database' => getenv('DATABASE_NAME') ?: 'studytrack',
    'username' => getenv('DATABASE_USERNAME') ?: 'root',
    'password' => getenv('DATABASE_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// Global database connection
$pdo = null;

/**
 * Get database connection (singleton pattern)
 * @return PDO
 */
function getDbConnection() {
    global $pdo, $db_config;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $db_config['host'],
                $db_config['database'],
                $db_config['charset']
            );
            
            // For PlanetScale, add SSL requirement
            if (getenv('DATABASE_HOST')) {
                $db_config['options'][PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            
            $pdo = new PDO(
                $dsn,
                $db_config['username'],
                $db_config['password'],
                $db_config['options']
            );
        } catch (PDOException $e) {
            // Log error and show user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    return $pdo;
}

/**
 * Execute a query and return results
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery($sql, $params = []) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a query and return single row
 * @param string $sql
 * @param array $params
 * @return array|false
 */
function dbQueryOne($sql, $params = []) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Execute an INSERT/UPDATE/DELETE query
 * @param string $sql
 * @param array $params
 * @return int Last insert ID or affected rows
 */
function dbExecute($sql, $params = []) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $pdo->lastInsertId() ?: $stmt->rowCount();
}

/**
 * Begin transaction
 */
function dbBeginTransaction() {
    $pdo = getDbConnection();
    $pdo->beginTransaction();
}

/**
 * Commit transaction
 */
function dbCommit() {
    $pdo = getDbConnection();
    $pdo->commit();
}

/**
 * Rollback transaction
 */
function dbRollback() {
    $pdo = getDbConnection();
    $pdo->rollBack();
}
