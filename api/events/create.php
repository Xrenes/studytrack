<?php
/**
 * API: Create Event
 * POST /api/events/create.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/db_functions.php';
require_once __DIR__ . '/../api_helper.php';

// Handle CORS and check authentication
handleCors();
requireApiAuth();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    // Get user info
    $user = getApiUser();
    $userSections = getUserSections($user['id']);
    
    // Get input data
    $data = getJsonInput();
    
    // Validate required fields
    validateRequired($data, ['date', 'type', 'title', 'section_id']);
    
    // Validate section access
    $sectionId = (int)$data['section_id'];
    if ($sectionId > 0 && !in_array($sectionId, $userSections)) {
        jsonError('You are not a member of this section', 403);
    }
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
        jsonError('Invalid date format. Use YYYY-MM-DD', 400);
    }
    
    // Validate type
    $validTypes = ['notice', 'assignment', 'exam', 'presentation', 'meeting', 'other'];
    if (!in_array($data['type'], $validTypes)) {
        jsonError('Invalid event type', 400);
    }
    
    // Create event
    $eventId = createEvent([
        'user_id' => $user['id'],
        'user_role' => $user['role'],
        'section_id' => $sectionId > 0 ? $sectionId : null,
        'date' => $data['date'],
        'time' => $data['time'] ?? null,
        'type' => $data['type'],
        'title' => trim($data['title']),
        'details' => trim($data['details'] ?? ''),
        'color' => $data['color'] ?? '#6B7280',
        'visibility' => $data['visibility'] ?? 'section',
        'priority' => $data['priority'] ?? 'medium'
    ]);
    
    // Get created event details
    $event = getEventById($eventId);
    
    jsonSuccess($event, 'Event created successfully');
    
} catch (Exception $e) {
    error_log("API Error (create event): " . $e->getMessage());
    jsonError('Failed to create event', 500);
}
