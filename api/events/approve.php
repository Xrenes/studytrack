<?php
/**
 * API: Approve Event
 * POST /api/events/approve.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/db_functions.php';
require_once __DIR__ . '/../api_helper.php';

handleCors();
requireApiTeacher();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    $user = getApiUser();
    $userSections = getUserSections($user['id']);
    
    $data = getJsonInput();
    validateRequired($data, ['event_id']);
    
    $eventId = (int)$data['event_id'];
    
    // Get event
    $event = getEventById($eventId);
    if (!$event) {
        jsonError('Event not found', 404);
    }
    
    // Verify teacher has access to this section
    if (!in_array($event['section_id'], $userSections)) {
        jsonError('You do not have permission to moderate this section', 403);
    }
    
    // Update status to approved
    updateEventStatus($eventId, 'approved', $user['id']);
    
    // Get updated event
    $updatedEvent = getEventById($eventId);
    
    jsonSuccess($updatedEvent, 'Event approved successfully');
    
} catch (Exception $e) {
    error_log("API Error (approve event): " . $e->getMessage());
    jsonError('Failed to approve event', 500);
}
