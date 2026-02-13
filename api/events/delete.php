<?php
/**
 * API: Delete Event
 * DELETE /api/events/delete.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/db_functions.php';
require_once __DIR__ . '/../api_helper.php';

handleCors();
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    $user = getApiUser();
    
    // Support both JSON body and query parameter
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = getJsonInput();
    } else {
        $data = $_POST;
    }
    
    validateRequired($data, ['event_id']);
    
    $eventId = (int)$data['event_id'];
    
    // Get existing event
    $event = getEventById($eventId);
    if (!$event) {
        jsonError('Event not found', 404);
    }
    
    // Check ownership or teacher role
    if ($event['user_id'] !== $user['id'] && $user['role'] !== 'teacher') {
        jsonError('You do not have permission to delete this event', 403);
    }
    
    // Delete event
    deleteEvent($eventId);
    
    jsonSuccess(['event_id' => $eventId], 'Event deleted successfully');
    
} catch (Exception $e) {
    error_log("API Error (delete event): " . $e->getMessage());
    jsonError('Failed to delete event', 500);
}
