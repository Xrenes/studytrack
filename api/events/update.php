<?php
/**
 * API: Update Event
 * PUT /api/events/update.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/db_functions.php';
require_once __DIR__ . '/../api_helper.php';

handleCors();
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonError('Method not allowed', 405);
}

try {
    $user = getApiUser();
    $data = getJsonInput();
    
    validateRequired($data, ['event_id']);
    
    $eventId = (int)$data['event_id'];
    
    // Get existing event
    $event = getEventById($eventId);
    if (!$event) {
        jsonError('Event not found', 404);
    }
    
    // Check ownership or teacher role
    if ($event['user_id'] !== $user['id'] && $user['role'] !== 'teacher') {
        jsonError('You do not have permission to edit this event', 403);
    }
    
    // Prepare update data (only include fields that are provided)
    $updateData = [];
    $allowedFields = ['date', 'time', 'type', 'title', 'details', 'color', 'completed'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateData[$field] = $data[$field];
        }
    }
    
    if (empty($updateData)) {
        jsonError('No fields to update', 400);
    }
    
    // Update event
    $affected = updateEvent($eventId, $updateData);
    
    // Get updated event
    $updatedEvent = getEventById($eventId);
    
    jsonSuccess($updatedEvent, 'Event updated successfully');
    
} catch (Exception $e) {
    error_log("API Error (update event): " . $e->getMessage());
    jsonError('Failed to update event', 500);
}
