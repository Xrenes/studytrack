<?php
/**
 * API: Get Events
 * GET /api/events/list.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/db_functions.php';
require_once __DIR__ . '/../api_helper.php';

handleCors();
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $user = getApiUser();
    $userSections = getUserSections($user['id']);
    
    // Get events
    $events = getUserEvents($user['id'], $user['role'], $userSections);
    
    // Apply optional filters from query parameters
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    if ($filter === 'upcoming') {
        $events = array_filter($events, function($event) {
            return strtotime($event['date']) >= strtotime(date('Y-m-d'));
        });
    } elseif ($filter === 'completed') {
        $events = array_filter($events, function($event) {
            return $event['completed'];
        });
    } elseif ($filter === 'pending') {
        $events = array_filter($events, function($event) use ($user) {
            return $event['status'] === 'pending' && $event['user_id'] === $user['id'];
        });
    } elseif (in_array($filter, ['notice', 'assignment', 'exam', 'presentation', 'meeting', 'other'])) {
        $events = array_filter($events, function($event) use ($filter) {
            return $event['type'] === $filter;
        });
    }
    
    // Apply search
    if (!empty($search)) {
        $events = array_filter($events, function($event) use ($search) {
            return stripos($event['title'], $search) !== false || 
                   stripos($event['details'], $search) !== false;
        });
    }
    
    // Re-index array
    $events = array_values($events);
    
    jsonSuccess([
        'events' => $events,
        'count' => count($events)
    ]);
    
} catch (Exception $e) {
    error_log("API Error (list events): " . $e->getMessage());
    jsonError('Failed to retrieve events', 500);
}
