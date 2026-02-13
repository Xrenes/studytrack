<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db_functions.php';

// Require login
requireLogin();

$user = getCurrentUser();
$userSections = getUserSections($user['id']);
$userEvents = getUserEvents($user['id'], $user['role'], $userSections);

// Calculate statistics
$totalEvents = count($userEvents);
$upcomingEvents = count(array_filter($userEvents, function($event) {
    return strtotime($event['date']) >= strtotime(date('Y-m-d'));
}));

// Role-specific stats
$sectionsCount = count($userSections);
if ($user['role'] === 'teacher') {
    // Count sections where user is creator
    $createdSections = array_filter($DUMMY_SECTIONS, function($section) use ($user) {
        return $section['creator_id'] == $user['id'];
    });
    $managedSections = count($createdSections);
} else {
    // For students, count assignments/exams
    $assignments = array_filter($userEvents, function($event) {
        return $event['type'] === 'assignment';
    });
    $assignmentsCount = count($assignments);
}

$pageTitle = 'Profile';
include BASE_PATH . '/includes/header.php';
?>

<style>
    .profile-page {
        padding-bottom: 100px;
    }
    
    .profile-page .page-header h1 {
        text-align: center;
        font-weight: 700;
        width: 100%;
        justify-content: center;
    }
    .profile-page .page-header {
        justify-content: center;
    }
    
    .profile-container {
        max-width: 600px;
        margin: 0 auto;
        padding: var(--spacing-lg);
    }
    
    .profile-header {
        text-align: center;
        padding: var(--spacing-xl) 0;
    }
    
    .profile-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--brand-blue), var(--brand-purple));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
        color: white;
        margin: 0 auto var(--spacing-md);
    }
    
    .profile-name {
        font-size: var(--font-size-xl);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .profile-id {
        font-size: var(--font-size-base);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-sm);
    }
    
    .profile-email {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    
    .stats-box {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-md);
        background: var(--bg-tertiary);
        padding: var(--spacing-lg);
        border-radius: var(--radius-lg);
        margin: var(--spacing-xl) 0;
    }
    
    @media (min-width: 600px) {
        .stats-box.teacher-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: var(--font-size-2xl);
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .stat-label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    
    .menu-section {
        margin: var(--spacing-xl) 0;
    }
    
    .menu-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-md) var(--spacing-lg);
        background: var(--bg-secondary);
        border: 1px solid var(--bg-tertiary);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-sm);
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: var(--text-primary);
    }
    
    .menu-item:hover {
        background: var(--bg-tertiary);
        transform: translateX(4px);
    }
    
    .menu-icon {
        width: 24px;
        height: 24px;
        color: var(--text-secondary);
    }
    
    .menu-text {
        flex: 1;
        font-size: var(--font-size-base);
        font-weight: 500;
    }
    
    .menu-arrow {
        width: 20px;
        height: 20px;
        color: var(--text-tertiary);
    }
    
    .logout-btn {
        width: 100%;
        margin-top: var(--spacing-xl);
        background: var(--error);
        color: white;
    }
    
    .logout-btn:hover {
        background: #DC2626;
    }
    
    .role-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: var(--radius-full);
        font-size: var(--font-size-xs);
        font-weight: 600;
        margin-top: var(--spacing-sm);
    }
    
    .role-student {
        background: rgba(59, 130, 246, 0.2);
        color: var(--color-assignment);
    }
    
    .role-teacher {
        background: rgba(139, 92, 246, 0.2);
        color: var(--color-exam);
    }
    
    .role-personal {
        background: rgba(249, 115, 22, 0.2);
        color: var(--color-notice);
    }
</style>

<div class="page-wrapper profile-page">
    <div class="page-header">
        <h1>Profile</h1>
    </div>
    
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-photo">
                <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
            </div>
            <div class="profile-name">
                <?php echo htmlspecialchars($user['name']); ?>
                <?php if ($user['student_id']): ?>
                    <?php echo htmlspecialchars($user['student_id']); ?>
                <?php endif; ?>
            </div>
            <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
            <span class="role-badge role-<?php echo $user['role']; ?>">
                <?php echo ucfirst($user['role']); ?>
            </span>
            <?php if (isset($_SESSION['selected_section_name'])): ?>
            <div class="selected-section" style="margin-top: var(--spacing-md); font-size: var(--font-size-sm); color: var(--brand-blue);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <?php echo htmlspecialchars($_SESSION['selected_section_name']); ?> (<?php echo htmlspecialchars($_SESSION['selected_section_code']); ?>)
            </div>
            <?php endif; ?>
        </div>
        
        <div class="stats-box <?php echo $user['role'] === 'teacher' ? 'teacher-stats' : ''; ?>">
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalEvents; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $upcomingEvents; ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
            <?php if ($user['role'] === 'teacher'): ?>
            <div class="stat-item">
                <div class="stat-value"><?php echo $managedSections; ?></div>
                <div class="stat-label">My Sections</div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="menu-section">
            <a href="/pages/sections.php" class="menu-item">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="menu-text">Sections</span>
                <svg class="menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
            
            <div class="menu-item" onclick="toggleTheme()">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="themeIcon">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <span class="menu-text">Dark Mode</span>
                <span class="text-secondary text-small" id="themeStatus">On</span>
            </div>
            
            <div class="menu-item" onclick="alert('Edit profile feature coming soon!')">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span class="menu-text">Edit Profile</span>
                <svg class="menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>
        
        <a href="/auth/logout.php" class="btn logout-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Logout
        </a>
    </div>
</div>

<script>
function toggleTheme() {
    // Theme toggle coming soon
    alert('Theme toggle feature coming soon!');
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
