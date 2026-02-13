<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db_functions.php';

// Require teacher role
requireTeacher();

$user = getCurrentUser();
$userSections = getUserSections($user['id']);

// Handle approve event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_event'])) {
    $eventId = (int)$_POST['event_id'];
    try {
        updateEventStatus($eventId, 'approved', $user['id']);
        $_SESSION['success_message'] = 'Event approved successfully!';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Failed to approve event.';
        error_log("Approve event error: " . $e->getMessage());
    }
    header('Location: /pages/moderate.php');
    exit;
}

// Handle reject event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_event'])) {
    $eventId = (int)$_POST['event_id'];
    try {
        updateEventStatus($eventId, 'rejected', $user['id']);
        $_SESSION['success_message'] = 'Event rejected.';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Failed to reject event.';
        error_log("Reject event error: " . $e->getMessage());
    }
    header('Location: /pages/moderate.php');
    exit;
}

// Get pending events in teacher's sections using database function
$pendingEvents = getPendingEventsForTeacher($user['id'], $userSections);

$pageTitle = 'Moderate Events';
include BASE_PATH . '/includes/header.php';
?>

<style>
    .moderate-page {
        padding-bottom: 100px;
    }
    
    .moderate-container {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--spacing-lg);
    }
    
    .page-intro {
        margin-bottom: var(--spacing-xl);
        color: var(--text-secondary);
    }
    
    .pending-count {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm) var(--spacing-md);
        background: rgba(249, 115, 22, 0.1);
        border: 1px solid var(--pending);
        border-radius: var(--radius-md);
        color: var(--pending);
        font-weight: 600;
        margin-bottom: var(--spacing-xl);
    }
    
    .event-card {
        background: var(--bg-secondary);
        border: 1px solid var(--bg-tertiary);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        transition: all 0.2s ease;
    }
    
    .event-card:hover {
        border-color: var(--brand-blue);
        box-shadow: var(--shadow-md);
    }
    
    .event-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--spacing-md);
    }
    
    .event-title {
        font-size: var(--font-size-lg);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
    }
    
    .event-meta {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-md);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-md);
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .event-details {
        background: var(--bg-tertiary);
        padding: var(--spacing-md);
        border-radius: var(--radius-sm);
        margin-bottom: var(--spacing-md);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        line-height: 1.6;
    }
    
    .event-actions {
        display: flex;
        gap: var(--spacing-sm);
        justify-content: flex-end;
    }
    
    .waiting-time {
        font-size: var(--font-size-xs);
        color: var(--warning);
        margin-top: var(--spacing-sm);
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
    
    .success-message {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid var(--success);
        color: var(--success);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
        font-size: var(--font-size-sm);
    }
</style>

<div class="page-wrapper moderate-page">
    <div class="page-header">
        <h1>Moderate Events</h1>
    </div>
    
    <div class="moderate-container">
        <div class="page-intro">
            Review and approve events submitted by students in your sections. Events will be visible to all section members once approved.
        </div>
        
        <?php if (count($pendingEvents) > 0): ?>
        <div class="pending-count">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <?php echo count($pendingEvents); ?> pending approval
        </div>
        
        <?php foreach ($pendingEvents as $event): ?>
            <div class="event-card">
                <div class="event-header">
                    <div style="flex: 1;">
                        <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                        <div class="event-meta">
                            <div class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </rect>
                                <?php echo date('l, F j, Y', strtotime($event['date'])); ?>
                                <?php if ($event['time']): ?>
                                    at <?php echo date('g:i A', strtotime($event['time'])); ?>
                                <?php endif; ?>
                            </div>
                            <div class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?php echo htmlspecialchars($event['creator_name']); ?>
                            </div>
                            <div class="meta-item">
                                <span class="badge" style="background: <?php echo $event['color']; ?>20; color: <?php echo $event['color']; ?>;">
                                    <?php echo ucfirst($event['type']); ?>
                                </span>
                            </div>
                            <div class="meta-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <?php echo htmlspecialchars($event['section_name']); ?>
                            </div>
                        </div>
                        <?php
                        $createdTime = strtotime($event['created_at']);
                        $hoursAgo = floor((time() - $createdTime) / 3600);
                        if ($hoursAgo > 0):
                        ?>
                        <div class="waiting-time">
                            ⏱️ Waiting for <?php echo $hoursAgo; ?> hour<?php echo $hoursAgo != 1 ? 's' : ''; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($event['details']): ?>
                <div class="event-details">
                    <strong>Details:</strong><br>
                    <?php echo nl2br(htmlspecialchars($event['details'])); ?>
                </div>
                <?php endif; ?>
                
                <div class="event-actions">
                    <button class="btn btn-secondary" onclick="rejectEvent(<?php echo $event['id']; ?>)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Reject
                    </button>
                    <button class="btn btn-success" onclick="approveEvent(<?php echo $event['id']; ?>)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Approve
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">✅</div>
            <div class="empty-text">
                <strong>All caught up!</strong><br>
                No pending events require your approval.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include API Client -->
<script src="/assets/js/api-client.js"></script>

<script>
// Enhanced approve/reject with AJAX
async function approveEventAjax(eventId) {
    if (!await StudyTrackUI.confirm('Are you sure you want to approve this event? It will be visible to all section members.')) {
        return;
    }
    
    try {
        const spinner = StudyTrackUI.showLoading();
        const response = await StudyTrackAPI.events.approve(eventId);
        StudyTrackUI.hideLoading(spinner);
        
        StudyTrackUI.showSuccess('Event approved successfully!');
        setTimeout(() => location.reload(), 500);
        
    } catch (error) {
        StudyTrackUI.showError(error.message || 'Failed to approve event');
    }
}

async function rejectEventAjax(eventId) {
    if (!await StudyTrackUI.confirm('Are you sure you want to reject this event? The creator will be notified.')) {
        return;
    }
    
    try {
        const spinner = StudyTrackUI.showLoading();
        const response = await StudyTrackAPI.events.reject(eventId);
        StudyTrackUI.hideLoading(spinner);
        
        StudyTrackUI.showSuccess('Event rejected successfully!');
        setTimeout(() => location.reload(), 500);
        
    } catch (error) {
        StudyTrackUI.showError(error.message || 'Failed to reject event');
    }
}

// Keep backward compatibility
function approveEvent(eventId) {
    // Use AJAX if API client is available
    if (window.StudyTrackAPI) {
        approveEventAjax(eventId);
        return;
    }
    
    // Fallback to form submission
    if (confirm('Are you sure you want to approve this event? It will be visible to all section members.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'approve_event';
        input1.value = '1';
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'event_id';
        input2.value = eventId;
        
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectEvent(eventId) {
    // Use AJAX if API client is available
    if (window.StudyTrackAPI) {
        rejectEventAjax(eventId);
        return;
    }
    
    // Fallback to form submission
    if (confirm('Are you sure you want to reject this event? The creator will be notified.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'reject_event';
        input1.value = '1';
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'event_id';
        input2.value = eventId;
        
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
