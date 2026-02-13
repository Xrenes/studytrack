<?php
/**
 * API: Leave Section
 * POST /api/sections/leave.php
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
    
    // Check if user is member
    if (!isUserInSection($sectionId, $user['id'])) {
        jsonError('You are not a member of this section', 400);
    }
    
    // Don't allow leaving if user is section creator
    if ($section['creator_id'] == $user['id']) {
        jsonError('Section creators cannot leave their own sections', 400);
    }
    
    // Leave section
    leaveSection($sectionId, $user['id']);
    
    jsonSuccess([
        'section_id' => $sectionId,
        'section_name' => $section['name']
    ], 'Successfully left section');
    
} catch (Exception $e) {
    error_log("API Error (leave section): " . $e->getMessage());
    jsonError('Failed to leave section', 500);
}
