<?php
/**
 * Database Migration Script
 * Creates tables and migrates dummy data to database
 * 
 * RUN THIS ONCE to set up your database:
 * php database/migrate.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dummy_data.php';

echo "=== StudyTrack Database Migration ===\n\n";

try {
    $pdo = getDbConnection();
    
    // ============================================
    // STEP 1: Create Tables from schema.sql
    // ============================================
    
    echo "Step 1: Creating tables...\n";
    
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die("ERROR: schema.sql not found!\n");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Tables created successfully\n\n";
    
    // ============================================
    // STEP 2: Migrate Users
    // ============================================
    
    echo "Step 2: Migrating users...\n";
    
    $userIdMap = []; // Map old IDs to new IDs
    
    foreach ($DUMMY_USERS as $user) {
        // Check if user already exists
        $existing = dbQueryOne("SELECT id FROM users WHERE email = ?", [$user['email']]);
        
        if ($existing) {
            echo "  - Skipping {$user['email']} (already exists)\n";
            $userIdMap[$user['id']] = $existing['id'];
            continue;
        }
        
        // Hash the password
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, student_id, role, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $newId = dbExecute($sql, [
            $user['name'],
            $user['email'],
            $hashedPassword,
            $user['student_id'],
            $user['role']
        ]);
        
        $userIdMap[$user['id']] = $newId;
        echo "  - Created user: {$user['name']} ({$user['email']}) - Plain password: {$user['password']}\n";
    }
    
    echo "✓ Users migrated successfully\n\n";
    
    // ============================================
    // STEP 3: Migrate Sections
    // ============================================
    
    echo "Step 3: Migrating sections...\n";
    
    $sectionIdMap = [];
    
    foreach ($DUMMY_SECTIONS as $section) {
        // Check if section already exists
        $existing = dbQueryOne("SELECT id FROM sections WHERE code = ?", [$section['code']]);
        
        if ($existing) {
            echo "  - Skipping {$section['code']} (already exists)\n";
            $sectionIdMap[$section['id']] = $existing['id'];
            continue;
        }
        
        $sql = "INSERT INTO sections (name, code, description, creator_id, academic_year, semester, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $newId = dbExecute($sql, [
            $section['name'],
            $section['code'],
            $section['description'],
            $userIdMap[$section['creator_id']],
            $section['academic_year'],
            $section['semester']
        ]);
        
        $sectionIdMap[$section['id']] = $newId;
        echo "  - Created section: {$section['name']} ({$section['code']})\n";
    }
    
    echo "✓ Sections migrated successfully\n\n";
    
    // ============================================
    // STEP 4: Migrate Section Members
    // ============================================
    
    echo "Step 4: Migrating section members...\n";
    
    foreach ($DUMMY_SECTIONS as $section) {
        $newSectionId = $sectionIdMap[$section['id']];
        
        foreach ($section['members'] as $oldUserId) {
            $newUserId = $userIdMap[$oldUserId];
            
            // Check if membership already exists
            $existing = dbQueryOne(
                "SELECT id FROM section_members WHERE section_id = ? AND user_id = ?",
                [$newSectionId, $newUserId]
            );
            
            if ($existing) {
                continue;
            }
            
            dbExecute(
                "INSERT INTO section_members (section_id, user_id, joined_at) VALUES (?, ?, NOW())",
                [$newSectionId, $newUserId]
            );
        }
        
        $memberCount = count($section['members']);
        echo "  - Added {$memberCount} members to {$section['code']}\n";
    }
    
    echo "✓ Section members migrated successfully\n\n";
    
    // ============================================
    // STEP 5: Migrate Events
    // ============================================
    
    echo "Step 5: Migrating events...\n";
    
    foreach ($DUMMY_EVENTS as $event) {
        // Check if event already exists (by title and date)
        $existing = dbQueryOne(
            "SELECT id FROM events WHERE title = ? AND date = ? AND user_id = ?",
            [$event['title'], $event['date'], $userIdMap[$event['user_id']]]
        );
        
        if ($existing) {
            echo "  - Skipping '{$event['title']}' (already exists)\n";
            continue;
        }
        
        $sql = "INSERT INTO events (user_id, section_id, date, time, type, title, details, color, status, visibility, priority, completed, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        dbExecute($sql, [
            $userIdMap[$event['user_id']],
            $event['section_id'] ? $sectionIdMap[$event['section_id']] : null,
            $event['date'],
            $event['time'],
            $event['type'],
            $event['title'],
            $event['details'],
            $event['color'],
            $event['status'],
            $event['visibility'] ?? 'section',
            $event['priority'] ?? 'medium',
            $event['completed'] ? 1 : 0
        ]);
        
        echo "  - Created event: {$event['title']} on {$event['date']}\n";
    }
    
    echo "✓ Events migrated successfully\n\n";
    
    // ============================================
    // MIGRATION SUMMARY
    // ============================================
    
    echo "=== Migration Complete! ===\n\n";
    
    $userCount = dbQueryOne("SELECT COUNT(*) as count FROM users", [])['count'];
    $sectionCount = dbQueryOne("SELECT COUNT(*) as count FROM sections", [])['count'];
    $memberCount = dbQueryOne("SELECT COUNT(*) as count FROM section_members", [])['count'];
    $eventCount = dbQueryOne("SELECT COUNT(*) as count FROM events", [])['count'];
    
    echo "Database Summary:\n";
    echo "  - Users: {$userCount}\n";
    echo "  - Sections: {$sectionCount}\n";
    echo "  - Section Members: {$memberCount}\n";
    echo "  - Events: {$eventCount}\n\n";
    
    echo "Login Credentials (use these to test):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($DUMMY_USERS as $user) {
        echo "  {$user['role']}: {$user['email']} / {$user['password']}\n";
    }
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "Next Steps:\n";
    echo "1. Update your pages to use db_functions.php instead of dummy_data.php\n";
    echo "2. Test login with the credentials above\n";
    echo "3. Verify that events and sections display correctly\n\n";
    
} catch (Exception $e) {
    echo "ERROR: Migration failed!\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
