<?php
// Dummy data for development (Phase 1)

// Dummy users for testing
$DUMMY_USERS = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'student_id' => '241-15-101',
        'email' => 'student@diu.edu.bd',
        'password' => 'student123', // In real app, this would be hashed
        'role' => 'student'
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith',
        'student_id' => '241-15-102',
        'email' => 'jane@diu.edu.bd',
        'password' => 'student123',
        'role' => 'student'
    ],
    [
        'id' => 3,
        'name' => 'Prof. Sarah Wilson',
        'student_id' => 'T-001',
        'email' => 'teacher@diu.edu.bd',
        'password' => 'teacher123',
        'role' => 'teacher'
    ],
    [
        'id' => 4,
        'name' => 'Alex Johnson',
        'student_id' => null,
        'email' => 'personal@gmail.com',
        'password' => 'personal123',
        'role' => 'personal'
    ]
];

// Dummy sections
$DUMMY_SECTIONS = [
    [
        'id' => 1,
        'name' => 'CSE 101 - Programming',
        'code' => 'CSE101-A',
        'description' => 'Introduction to Programming',
        'creator_id' => 3,
        'members' => [1, 2, 3],
        'academic_year' => '2025-26',
        'semester' => 'Spring'
    ],
    [
        'id' => 2,
        'name' => 'Data Structures',
        'code' => 'CSE201-B',
        'description' => 'Advanced Data Structures',
        'creator_id' => 3,
        'members' => [1, 3],
        'academic_year' => '2025-26',
        'semester' => 'Spring'
    ],
    [
        'id' => 3,
        'name' => 'Database Systems',
        'code' => 'CSE301-A',
        'description' => 'Relational Database Design',
        'creator_id' => 3,
        'members' => [2, 3],
        'academic_year' => '2025-26',
        'semester' => 'Fall'
    ],
    [
        'id' => 4,
        'name' => 'Section 66-B',
        'code' => '66_B',
        'description' => 'Default student section',
        'creator_id' => 3,
        'members' => [1, 2],
        'academic_year' => '2025-26',
        'semester' => 'Spring'
    ]
];

// Dummy events
// IMPORTANT: Event Interconnection System
// =====================================
// All events with a section_id are SHARED across all members of that section.
// This means:
// 1. When a teacher or student creates an event for a section, ALL members see it
// 2. When an event is edited by anyone with permission, the changes appear for ALL members
// 3. When an event is deleted, it's removed for ALL members
// 4. Events are filtered by the getUserEvents() function based on:
//    - User's section membership
//    - Event approval status (approved events visible to all, pending only to creator and teachers)
//    - User role (teachers see all section events, students see approved + their own)
//
// This ensures students and teachers in the same section stay synchronized.
$DUMMY_EVENTS = [
    [
        'id' => 1,
        'user_id' => 3,
        'section_id' => 1,
        'date' => '2026-07-07',
        'time' => '10:00',
        'type' => 'exam',
        'title' => 'Mid Exam',
        'details' => 'Chapters 1-5. Bring your student ID.',
        'color' => '#8B5CF6',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-06-20 10:00:00',
        'creator_name' => 'Prof. Sarah Wilson'
    ],
    [
        'id' => 2,
        'user_id' => 1,
        'section_id' => 1,
        'date' => '2026-07-16',
        'time' => null,
        'type' => 'assignment',
        'title' => 'Assignment 1',
        'details' => 'Complete all exercises from chapter 3',
        'color' => '#3B82F6',
        'status' => 'pending',
        'visibility' => 'public',
        'priority' => 'medium',
        'completed' => false,
        'created_at' => '2026-07-01 14:30:00',
        'creator_name' => 'John Doe'
    ],
    [
        'id' => 3,
        'user_id' => 3,
        'section_id' => 1,
        'date' => '2026-07-22',
        'time' => '14:00',
        'type' => 'exam',
        'title' => 'Final Exam',
        'details' => 'Comprehensive final examination',
        'color' => '#8B5CF6',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-06-25 09:00:00',
        'creator_name' => 'Prof. Sarah Wilson'
    ],
    [
        'id' => 4,
        'user_id' => 2,
        'section_id' => 1,
        'date' => '2026-07-10',
        'time' => '15:30',
        'type' => 'presentation',
        'title' => 'Project Presentation',
        'details' => 'Group project demonstration',
        'color' => '#10B981',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'medium',
        'completed' => false,
        'created_at' => '2026-07-05 11:20:00',
        'creator_name' => 'Jane Smith'
    ],
    [
        'id' => 5,
        'user_id' => 4,
        'section_id' => null,
        'date' => '2026-07-15',
        'time' => '09:00',
        'type' => 'meeting',
        'title' => 'Personal Meeting',
        'details' => 'Doctor appointment',
        'color' => '#F59E0B',
        'status' => 'approved',
        'visibility' => 'private',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-07-10 08:00:00',
        'creator_name' => 'Alex Johnson'
    ],
    [
        'id' => 6,
        'user_id' => 1,
        'section_id' => 2,
        'date' => '2026-08-05',
        'time' => null,
        'type' => 'assignment',
        'title' => 'Data Structure Project',
        'details' => 'Implement binary search tree',
        'color' => '#3B82F6',
        'status' => 'pending',
        'visibility' => 'public',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-07-28 16:00:00',
        'creator_name' => 'John Doe'
    ],
    // Section 66_B events - shared between students 1 and 2
    [
        'id' => 7,
        'user_id' => 3,
        'section_id' => 4, // 66_B section
        'date' => '2026-07-18',
        'time' => '09:00',
        'type' => 'exam',
        'title' => 'Section 66_B Midterm',
        'details' => 'All topics from week 1-7. Bring calculator and ID.',
        'color' => '#8B5CF6',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-07-01 10:00:00',
        'creator_name' => 'Prof. Sarah Wilson'
    ],
    [
        'id' => 8,
        'user_id' => 1,
        'section_id' => 4, // 66_B section
        'date' => '2026-07-25',
        'time' => null,
        'type' => 'assignment',
        'title' => '66_B Group Assignment',
        'details' => 'Collaborative project - work in groups of 3-4',
        'color' => '#3B82F6',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'medium',
        'completed' => false,
        'created_at' => '2026-07-15 14:00:00',
        'creator_name' => 'John Doe'
    ],
    [
        'id' => 9,
        'user_id' => 2,
        'section_id' => 4, // 66_B section
        'date' => '2026-08-01',
        'time' => '14:30',
        'type' => 'presentation',
        'title' => '66_B Final Presentations',
        'details' => 'Present your semester project to the class',
        'color' => '#10B981',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-07-20 11:00:00',
        'creator_name' => 'Jane Smith'
    ],
    [
        'id' => 10,
        'user_id' => 3,
        'section_id' => 4, // 66_B section
        'date' => '2026-07-12',
        'time' => '10:00',
        'type' => 'notice',
        'title' => 'Class Location Change',
        'details' => 'Next week classes will be held in Room 401 instead of 301',
        'color' => '#F97316',
        'status' => 'approved',
        'visibility' => 'public',
        'priority' => 'high',
        'completed' => false,
        'created_at' => '2026-07-08 09:00:00',
        'creator_name' => 'Prof. Sarah Wilson'
    ]
];

// Event type colors
$EVENT_COLORS = [
    'notice' => '#F97316',
    'assignment' => '#3B82F6',
    'exam' => '#8B5CF6',
    'presentation' => '#10B981',
    'meeting' => '#F59E0B',
    'other' => '#6B7280'
];

// Helper function to get events for a user
// This ensures all students and teachers from the same section see the same events
function getUserEvents($userId, $userRole, $userSections) {
    global $DUMMY_EVENTS;
    $visibleEvents = [];
    $addedEventIds = []; // Track already added events to avoid duplicates
    
    foreach ($DUMMY_EVENTS as $event) {
        // Skip if already added
        if (in_array($event['id'], $addedEventIds)) {
            continue;
        }
        
        // Personal events (no section_id) - only visible to creator
        if ($event['section_id'] === null) {
            if ($event['user_id'] == $userId) {
                $visibleEvents[] = $event;
                $addedEventIds[] = $event['id'];
            }
            continue;
        }
        
        // Section events - shared across all section members
        if ($event['section_id'] && in_array($event['section_id'], $userSections)) {
            // Teachers see ALL events in their sections (approved and pending)
            if ($userRole == 'teacher') {
                $visibleEvents[] = $event;
                $addedEventIds[] = $event['id'];
            }
            // Students see:
            // 1. Their own events (even if pending)
            // 2. Approved events from other section members
            elseif ($event['user_id'] == $userId || $event['status'] == 'approved') {
                $visibleEvents[] = $event;
                $addedEventIds[] = $event['id'];
            }
        }
    }
    
    return $visibleEvents;
}

// Helper function to get user's sections
function getUserSections($userId) {
    global $DUMMY_SECTIONS;
    $userSections = [];
    
    foreach ($DUMMY_SECTIONS as $section) {
        if (in_array($userId, $section['members'])) {
            $userSections[] = $section['id'];
        }
    }
    
    return $userSections;
}
?>
