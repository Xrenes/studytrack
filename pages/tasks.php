<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db_functions.php';

// Require login
requireLogin();

$user = getCurrentUser();
$userSections = getUserSections($user['id']);
$userEvents = getUserEvents($user['id'], $user['role'], $userSections);

// Get selected section info
$selectedSectionId = $_SESSION['selected_section_id'] ?? null;
$selectedSectionName = $_SESSION['selected_section_name'] ?? 'No section selected';
$selectedSectionCode = $_SESSION['selected_section_code'] ?? '';

// Filter logic
$filter = $_GET['filter'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Apply filters
$filteredEvents = $userEvents;

if ($filter === 'upcoming') {
    $filteredEvents = array_filter($filteredEvents, function($event) {
        return strtotime($event['date']) >= strtotime(date('Y-m-d'));
    });
} elseif ($filter === 'completed') {
    $filteredEvents = array_filter($filteredEvents, function($event) {
        return $event['completed'];
    });
} elseif ($filter === 'pending') {
    $filteredEvents = array_filter($filteredEvents, function($event) use ($user) {
        return $event['status'] === 'pending' && $event['user_id'] === $user['id'];
    });
} elseif (in_array($filter, ['notice', 'assignment', 'exam', 'presentation', 'meeting', 'other'])) {
    $filteredEvents = array_filter($filteredEvents, function($event) use ($filter) {
        return $event['type'] === $filter;
    });
}

// Apply search
if (!empty($searchQuery)) {
    $filteredEvents = array_filter($filteredEvents, function($event) use ($searchQuery) {
        return stripos($event['title'], $searchQuery) !== false || 
               stripos($event['details'], $searchQuery) !== false;
    });
}

// Sort by date
usort($filteredEvents, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

$pageTitle = 'Tasks';
include BASE_PATH . '/includes/header.php';
?>

<style>
    .tasks-page {
        padding-bottom: 100px;
    }
    
    .tasks-page .page-header h1 {
        text-align: center;
        font-weight: 700;
        width: 100%;
        justify-content: center;
    }
    
    .tasks-page .page-header {
        justify-content: center;
    }
    
    .tasks-container {
        padding: var(--spacing-md);
        padding-bottom: var(--spacing-lg);
        max-width: 600px;
        margin: 0 auto;
    }
    
    .filter-buttons {
        display: flex;
        gap: var(--spacing-sm);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-sm);
    }
    
    .filter-buttons::-webkit-scrollbar {
        display: none;
    }
    
    .filter-btn {
        padding: 8px 16px;
        background: var(--bg-tertiary);
        border: 1px solid transparent;
        border-radius: var(--radius-full);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    
    .filter-btn:hover {
        background: var(--bg-primary);
    }
    
    .filter-btn.active {
        background: var(--brand-blue);
        color: white;
    }
    
    .search-box {
        position: relative;
        margin-bottom: var(--spacing-lg);
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 48px 10px 16px;
        background: var(--bg-tertiary);
        border: 1px solid transparent;
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
    }
    
    .search-box input:focus {
        border-color: var(--brand-blue);
    }
    
    .search-btn {
        position: absolute;
        right: 4px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--brand-blue);
        border: none;
        border-radius: var(--radius-sm);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: white;
    }
    
    .search-btn:hover {
        background: var(--brand-blue-hover);
        transform: translateY(-50%) scale(1.05);
    }
    
    .search-btn:active {
        transform: translateY(-50%) scale(0.95);
    }
    
    .task-card {
        display: grid;
        grid-template-columns: 60px 1fr auto;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        background: var(--bg-secondary);
        border: 1px solid var(--bg-tertiary);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-md);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .task-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .task-content {
        flex: 1;
        min-width: 0;
    }
    
    .task-title {
        font-weight: 600;
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .task-subtitle {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    
    .task-date {
        text-align: right;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        white-space: nowrap;
    }
    
    .empty-state {
        text-align: center;
        padding: var(--spacing-2xl);
    }
    
    .empty-icon {
        font-size: 64px;
        margin-bottom: var(--spacing-md);
        opacity: 0.3;
    }
    
    .empty-text {
        color: var(--text-secondary);
        font-size: var(--font-size-base);
    }
</style>

<div class="page-wrapper tasks-page">
    <div class="page-header">
        <h1>Tasks</h1>
    </div>
    
    <div class="tasks-container">
        <?php if ($selectedSectionId): ?>
        <div style="background: rgba(43, 127, 214, 0.1); border: 1px solid rgba(43, 127, 214, 0.3); border-radius: var(--radius-md); padding: var(--spacing-sm) var(--spacing-md); margin-bottom: var(--spacing-md); font-size: var(--font-size-sm); color: var(--brand-blue);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <strong>Section Mode:</strong> Viewing tasks for <?php echo htmlspecialchars($selectedSectionName); ?> (<?php echo htmlspecialchars($selectedSectionCode); ?>). 
            All tasks are shared with everyone in this section.
        </div>
        <?php endif; ?>
        
        <div class="filter-buttons">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?filter=upcoming" class="filter-btn <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
            <a href="?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?filter=exam" class="filter-btn <?php echo $filter === 'exam' ? 'active' : ''; ?>">Exams</a>
            <a href="?filter=assignment" class="filter-btn <?php echo $filter === 'assignment' ? 'active' : ''; ?>">Assignments</a>
            <a href="?filter=presentation" class="filter-btn <?php echo $filter === 'presentation' ? 'active' : ''; ?>">Presentations</a>
        </div>
        
        <form method="GET" class="search-box">
            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
            <input type="text" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit" class="search-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
        </form>
        <?php if (empty($filteredEvents)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <div class="empty-text">
                    <?php if (!empty($searchQuery)): ?>
                        No events found matching "<?php echo htmlspecialchars($searchQuery); ?>"
                    <?php else: ?>
                        No events found
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($filteredEvents as $event): ?>
                <div class="task-card" onclick="openEventModal(<?php echo $event['id']; ?>)">
                    <div class="task-icon" style="background: <?php echo htmlspecialchars($event['color']); ?>20; color: <?php echo htmlspecialchars($event['color']); ?>;">
                        <?php
                        $icons = [
                            'exam' => '⭐',
                            'assignment' => '✓',
                            'presentation' => '📊',
                            'notice' => '📢',
                            'meeting' => '🤝',
                            'other' => '📝'
                        ];
                        echo $icons[$event['type']] ?? '📝';
                        ?>
                    </div>
                    <div class="task-content">
                        <div class="task-title"><?php echo htmlspecialchars($event['title']); ?></div>
                        <div class="task-subtitle">
                            <?php echo htmlspecialchars($event['creator_name']); ?>
                            <?php if ($event['status'] === 'pending'): ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="task-date">
                        <?php echo date('M j', strtotime($event['date'])); ?><br>
                        <?php echo $event['time'] ? date('g:i A', strtotime($event['time'])) : ''; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Event Details</h2>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Event details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
const events = <?php echo json_encode($filteredEvents); ?>;

function openEventModal(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    document.getElementById('modalTitle').textContent = event.title;
    document.getElementById('modalBody').innerHTML = `
        <div style="margin-bottom: var(--spacing-md);">
            <span class="badge" style="background: ${event.color}20; color: ${event.color};">
                ${event.type.charAt(0).toUpperCase() + event.type.slice(1)}
            </span>
            ${event.status === 'pending' ? '<span class="badge badge-pending">Pending Approval</span>' : ''}
        </div>
        <div class="form-group">
            <label>Date & Time</label>
            <div>${new Date(event.date + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</div>
            ${event.time ? `<div>${event.time}</div>` : '<div class="text-secondary">All day</div>'}
        </div>
        <div class="form-group">
            <label>Created by</label>
            <div>${escapeHtml(event.creator_name)}</div>
        </div>
        ${event.details ? `
            <div class="form-group">
                <label>Details</label>
                <div style="color: var(--text-secondary);">${escapeHtml(event.details)}</div>
            </div>
        ` : ''}
    `;
    
    document.getElementById('eventModal').classList.add('active');
}

function closeModal() {
    document.getElementById('eventModal').classList.remove('active');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on overlay click
document.getElementById('eventModal').addEventListener('click', (e) => {
    if (e.target.id === 'eventModal') {
        closeModal();
    }
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
