<?php
/**
 * Database Functions - Replace dummy_data.php
 * All database query functions for StudyTrack
 */

require_once __DIR__ . '/database.php';

// ============================================
// USER FUNCTIONS
// ============================================

/**
 * Get user by email and role
 * @param string $email
 * @param string $role
 * @return array|false
 */
function getUserByEmail($email, $role = null) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $params = [$email];
    
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    return dbQueryOne($sql, $params);
}

/**
 * Get user by ID
 * @param int $userId
 * @return array|false
 */
function getUserById($userId) {
    return dbQueryOne("SELECT * FROM users WHERE id = ?", [$userId]);
}

/**
 * Create new user
 * @param array $data
 * @return int User ID
 */
function createUser($data) {
    $sql = "INSERT INTO users (name, email, password, student_id, role, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    return dbExecute($sql, [
        $data['name'],
        $data['email'],
        $data['password'], // Should already be hashed
        $data['student_id'] ?? null,
        $data['role'] ?? 'student'
    ]);
}

/**
 * Update user profile
 * @param int $userId
 * @param array $data
 * @return int Affected rows
 */
function updateUser($userId, $data) {
    $fields = [];
    $params = [];
    
    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $params[] = $data['name'];
    }
    if (isset($data['email'])) {
        $fields[] = "email = ?";
        $params[] = $data['email'];
    }
    if (isset($data['student_id'])) {
        $fields[] = "student_id = ?";
        $params[] = $data['student_id'];
    }
    if (isset($data['theme'])) {
        $fields[] = "theme = ?";
        $params[] = $data['theme'];
    }
    
    if (empty($fields)) {
        return 0;
    }
    
    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    
    return dbExecute($sql, $params);
}

// ============================================
// SECTION FUNCTIONS
// ============================================

/**
 * Get user's sections
 * @param int $userId
 * @return array Section IDs
 */
function getUserSections($userId) {
    $sql = "SELECT section_id FROM section_members 
            WHERE user_id = ? AND status = 'active'";
    
    $results = dbQuery($sql, [$userId]);
    return array_column($results, 'section_id');
}

/**
 * Get all sections with member count
 * @return array
 */
function getAllSections() {
    $sql = "SELECT s.*, 
            COUNT(sm.user_id) as member_count,
            u.name as creator_name
            FROM sections s
            LEFT JOIN section_members sm ON s.id = sm.section_id AND sm.status = 'active'
            LEFT JOIN users u ON s.creator_id = u.id
            GROUP BY s.id
            ORDER BY s.created_at DESC";
    
    return dbQuery($sql);
}

/**
 * Get section by ID
 * @param int $sectionId
 * @return array|false
 */
function getSectionById($sectionId) {
    return dbQueryOne("SELECT * FROM sections WHERE id = ?", [$sectionId]);
}

/**
 * Get section by code
 * @param string $code
 * @return array|false
 */
function getSectionByCode($code) {
    return dbQueryOne("SELECT * FROM sections WHERE code = ?", [$code]);
}

/**
 * Check if user is member of section
 * @param int $sectionId
 * @param int $userId
 * @return bool
 */
function isUserInSection($sectionId, $userId) {
    $sql = "SELECT COUNT(*) as count FROM section_members 
            WHERE section_id = ? AND user_id = ? AND status = 'active'";
    
    $result = dbQueryOne($sql, [$sectionId, $userId]);
    return $result['count'] > 0;
}

/**
 * Join a section
 * @param int $sectionId
 * @param int $userId
 * @return int
 */
function joinSection($sectionId, $userId) {
    // Check if already member (including inactive)
    $existing = dbQueryOne(
        "SELECT id, status FROM section_members WHERE section_id = ? AND user_id = ?",
        [$sectionId, $userId]
    );
    
    if ($existing) {
        // Reactivate if inactive
        if ($existing['status'] === 'inactive') {
            return dbExecute(
                "UPDATE section_members SET status = 'active', joined_at = NOW() WHERE id = ?",
                [$existing['id']]
            );
        }
        return 0; // Already active member
    }
    
    // Add new member
    return dbExecute(
        "INSERT INTO section_members (section_id, user_id, joined_at) VALUES (?, ?, NOW())",
        [$sectionId, $userId]
    );
}

/**
 * Leave a section
 * @param int $sectionId
 * @param int $userId
 * @return int
 */
function leaveSection($sectionId, $userId) {
    return dbExecute(
        "UPDATE section_members SET status = 'inactive' WHERE section_id = ? AND user_id = ?",
        [$sectionId, $userId]
    );
}

/**
 * Create new section
 * @param array $data
 * @return int Section ID
 */
function createSection($data) {
    $sql = "INSERT INTO sections (name, code, description, creator_id, academic_year, semester, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $sectionId = dbExecute($sql, [
        $data['name'],
        $data['code'],
        $data['description'] ?? null,
        $data['creator_id'],
        $data['academic_year'] ?? null,
        $data['semester'] ?? null
    ]);
    
    // Auto-join creator as member
    if ($sectionId) {
        joinSection($sectionId, $data['creator_id']);
    }
    
    return $sectionId;
}

// ============================================
// EVENT FUNCTIONS
// ============================================

/**
 * Get events for a user (same logic as dummy_data.php)
 * @param int $userId
 * @param string $userRole
 * @param array $userSections
 * @return array
 */
function getUserEvents($userId, $userRole, $userSections) {
    if (empty($userSections)) {
        // Only show personal events if user has no sections
        $sql = "SELECT e.*, u.name as creator_name, s.name as section_name, s.code as section_code
                FROM events e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN sections s ON e.section_id = s.id
                WHERE e.user_id = ? AND e.section_id IS NULL
                ORDER BY e.date ASC, e.time ASC";
        
        return dbQuery($sql, [$userId]);
    }
    
    // Build query based on role
    if ($userRole === 'teacher') {
        // Teachers see ALL events in their sections (approved and pending)
        $placeholders = implode(',', array_fill(0, count($userSections), '?'));
        $sql = "SELECT e.*, u.name as creator_name, s.name as section_name, s.code as section_code
                FROM events e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN sections s ON e.section_id = s.id
                WHERE (e.section_id IN ($placeholders) OR (e.user_id = ? AND e.section_id IS NULL))
                ORDER BY e.date ASC, e.time ASC";
        
        $params = array_merge($userSections, [$userId]);
    } else {
        // Students see:
        // 1. Their own events (even if pending)
        // 2. Approved events from other section members
        $placeholders = implode(',', array_fill(0, count($userSections), '?'));
        $sql = "SELECT e.*, u.name as creator_name, s.name as section_name, s.code as section_code
                FROM events e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN sections s ON e.section_id = s.id
                WHERE (
                    (e.section_id IN ($placeholders) AND (e.user_id = ? OR e.status = 'approved'))
                    OR (e.user_id = ? AND e.section_id IS NULL)
                )
                ORDER BY e.date ASC, e.time ASC";
        
        $params = array_merge($userSections, [$userId, $userId]);
    }
    
    return dbQuery($sql, $params);
}

/**
 * Get event by ID
 * @param int $eventId
 * @return array|false
 */
function getEventById($eventId) {
    $sql = "SELECT e.*, u.name as creator_name, s.name as section_name, s.code as section_code
            FROM events e
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN sections s ON e.section_id = s.id
            WHERE e.id = ?";
    
    return dbQueryOne($sql, [$eventId]);
}

/**
 * Get pending events for teacher's sections
 * @param int $teacherId
 * @param array $teacherSections
 * @return array
 */
function getPendingEventsForTeacher($teacherId, $teacherSections) {
    if (empty($teacherSections)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($teacherSections), '?'));
    $sql = "SELECT e.*, u.name as creator_name, s.name as section_name, s.code as section_code
            FROM events e
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN sections s ON e.section_id = s.id
            WHERE e.status = 'pending' 
            AND e.section_id IN ($placeholders)
            AND e.user_id != ?
            ORDER BY e.created_at DESC";
    
    $params = array_merge($teacherSections, [$teacherId]);
    return dbQuery($sql, $params);
}

/**
 * Create new event
 * @param array $data
 * @return int Event ID
 */
function createEvent($data) {
    // Auto-approve teacher events, pending for students
    $status = ($data['user_role'] ?? 'student') === 'teacher' ? 'approved' : 'pending';
    
    $sql = "INSERT INTO events (user_id, section_id, date, time, type, title, details, color, status, visibility, priority, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    return dbExecute($sql, [
        $data['user_id'],
        $data['section_id'] ?? null,
        $data['date'],
        $data['time'] ?? null,
        $data['type'] ?? 'other',
        $data['title'],
        $data['details'] ?? '',
        $data['color'] ?? '#6B7280',
        $status,
        $data['visibility'] ?? 'section',
        $data['priority'] ?? 'medium'
    ]);
}

/**
 * Update event
 * @param int $eventId
 * @param array $data
 * @return int Affected rows
 */
function updateEvent($eventId, $data) {
    $fields = [];
    $params = [];
    
    if (isset($data['date'])) {
        $fields[] = "date = ?";
        $params[] = $data['date'];
    }
    if (isset($data['time'])) {
        $fields[] = "time = ?";
        $params[] = $data['time'];
    }
    if (isset($data['type'])) {
        $fields[] = "type = ?";
        $params[] = $data['type'];
    }
    if (isset($data['title'])) {
        $fields[] = "title = ?";
        $params[] = $data['title'];
    }
    if (isset($data['details'])) {
        $fields[] = "details = ?";
        $params[] = $data['details'];
    }
    if (isset($data['color'])) {
        $fields[] = "color = ?";
        $params[] = $data['color'];
    }
    if (isset($data['completed'])) {
        $fields[] = "completed = ?";
        $params[] = $data['completed'] ? 1 : 0;
    }
    
    if (empty($fields)) {
        return 0;
    }
    
    $params[] = $eventId;
    $sql = "UPDATE events SET " . implode(', ', $fields) . " WHERE id = ?";
    
    return dbExecute($sql, $params);
}

/**
 * Update event status (approve/reject)
 * @param int $eventId
 * @param string $status 'approved' or 'rejected'
 * @param int $approverId
 * @return int Affected rows
 */
function updateEventStatus($eventId, $status, $approverId) {
    $sql = "UPDATE events 
            SET status = ?, approved_by = ?, approved_at = NOW() 
            WHERE id = ?";
    
    return dbExecute($sql, [$status, $approverId, $eventId]);
}

/**
 * Delete event
 * @param int $eventId
 * @return int Affected rows
 */
function deleteEvent($eventId) {
    return dbExecute("DELETE FROM events WHERE id = ?", [$eventId]);
}

// ============================================
// GLOBAL SECTIONS ARRAY FOR COMPATIBILITY
// ============================================

// For pages that still need $DUMMY_SECTIONS array format
// Wrapped in try-catch to handle case where database isn't set up yet
$DUMMY_SECTIONS = [];
try {
    $DUMMY_SECTIONS = getAllSections();
    
    // Map to old format if needed
    foreach ($DUMMY_SECTIONS as &$section) {
        // Get members array
        $members = dbQuery(
            "SELECT user_id FROM section_members WHERE section_id = ? AND status = 'active'",
            [$section['id']]
        );
        $section['members'] = array_column($members, 'user_id');
    }
    unset($section);
} catch (Exception $e) {
    // Database not yet set up - migration needs to run
    error_log("Database not initialized: " . $e->getMessage());
}
