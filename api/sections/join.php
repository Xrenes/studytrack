<?php
/**
 * API: Join Section
 * POST /api/sections/join.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/db_functions.php';
require_once __DIR__ . '/../api_helper.php';

handleCors();
requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    $user = getApiUser();
    $data = getJsonInput();
    
    validateRequired($data, ['section_id']);
    
    $sectionId = (int)$data['section_id'];
    
    // Check if section exists
    $section = getSectionById($sectionId);
    if (!$section) {
        jsonError('Section not found', 404);
    }
    
    // Check if already member
    if (isUserInSection($sectionId, $user['id'])) {
        jsonError('You are already a member of this section', 400);
    }
    
    // For students, check if they're trying to join multiple sections
    if ($user['role'] === 'student') {
        $currentSections = getUserSections($user['id']);
        if (count($currentSections) >= 1) {
            jsonError('Students can only be in one section at a time', 400);
        }
    }
    
    // Join section
    joinSection($sectionId, $user['id']);
    
    jsonSuccess([
        'section_id' => $sectionId,
        'section_name' => $section['name'],
        'section_code' => $section['code']
    ], 'Successfully joined section');
    
} catch (Exception $e) {
    error_log("API Error (join section): " . $e->getMessage());
    jsonError('Failed to join section', 500);
}
