<?php
/**
 * API Helper Functions
 * Common utilities for API endpoints
 */

/**
 * Set JSON response headers
 */
function setJsonHeaders() {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
}

/**
 * Return JSON response and exit
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    setJsonHeaders();
    echo json_encode($data);
    exit;
}

/**
 * Return success response
 * @param mixed $data Success data
 * @param string $message Success message
 */
function jsonSuccess($data = null, $message = 'Success') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], 200);
}

/**
 * Return error response
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 */
function jsonError($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

/**
 * Require authentication for API endpoint
 */
function requireApiAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        jsonError('Authentication required', 401);
    }
}

/**
 * Require teacher role for API endpoint
 */
function requireApiTeacher() {
    requireApiAuth();
    if ($_SESSION['user_role'] !== 'teacher') {
        jsonError('Teacher access required', 403);
    }
}

/**
 * Get JSON input from request body
 * @return array Decoded JSON data
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonError('Invalid JSON input', 400);
    }
    
    return $data ?? [];
}

/**
 * Validate required fields in input
 * @param array $data Input data
 * @param array $required Required field names
 * @return bool True if all required fields present
 */
function validateRequired($data, $required) {
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            jsonError("Missing required field: $field", 400);
        }
    }
    return true;
}

/**
 * Get current API user
 * @return array User data
 */
function getApiUser() {
    requireApiAuth();
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'student_id' => $_SESSION['student_id'] ?? null
    ];
}

/**
 * Handle CORS for API requests
 */
function handleCors() {
    // Allow same-origin requests
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        exit(0);
    }
}
