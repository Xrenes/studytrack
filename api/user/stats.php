<?php
/**
 * API: Get User Stats
 * GET /api/user/stats.php
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
    $userEvents = getUserEvents($user['id'], $user['role'], $userSections);
    
    // Calculate stats
    $totalEvents = count($userEvents);
    
    $upcomingEvents = count(array_filter($userEvents, function($event) {
        return strtotime($event['date']) >= strtotime(date('Y-m-d'));
    }));
    
    $completedEvents = count(array_filter($userEvents, function($event) {
        return $event['completed'];
    }));
    
    $pendingEvents = count(array_filter($userEvents, function($event) use ($user) {
        return $event['status'] === 'pending' && $event['user_id'] === $user['id'];
    }));
    
    $stats = [
        'total_events' => $totalEvents,
        'upcoming_events' => $upcomingEvents,
        'completed_events' => $completedEvents,
        'pending_events' => $pendingEvents
    ];
    
    // Add teacher-specific stats
    if ($user['role'] === 'teacher') {
        $stats['my_sections'] = count($userSections);
        
        // Count pending moderation items
        $pendingModeration = getPendingEventsForTeacher($user['id'], $userSections);
        $stats['pending_moderation'] = count($pendingModeration);
    } else {
        $stats['my_sections'] = count($userSections);
    }
    
    jsonSuccess($stats);
    
} catch (Exception $e) {
    error_log("API Error (user stats): " . $e->getMessage());
    jsonError('Failed to retrieve stats', 500);
}
