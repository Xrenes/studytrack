<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db_functions.php';

// Require login
requireLogin();

// Handle event creation POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $user = getCurrentUser();
    
    try {
        createEvent([
            'user_id' => $user['id'],
            'user_role' => $user['role'],
            'section_id' => !empty($_POST['section_id']) ? (int)$_POST['section_id'] : null,
            'date' => $_POST['date'],
            'time' => !empty($_POST['time']) ? $_POST['time'] : null,
            'type' => $_POST['type'],
            'title' => $_POST['title'],
            'details' => $_POST['details'] ?? '',
            'color' => $_POST['color'] ?? '#6B7280',
            'visibility' => $_POST['visibility'] ?? 'section',
            'priority' => $_POST['priority'] ?? 'medium'
        ]);
        
        $_SESSION['success_message'] = 'Event created successfully!';
        header('Location: /pages/calendar.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Failed to create event. Please try again.';
        error_log("Event creation error: " . $e->getMessage());
    }
}

$user = getCurrentUser();
$userSections = getUserSections($user['id']);
$userEvents = getUserEvents($user['id'], $user['role'], $userSections);

// Get section details for the selected section
$selectedSectionId = $_SESSION['selected_section_id'] ?? null;
$selectedSectionName = $_SESSION['selected_section_name'] ?? 'No section selected';
$selectedSectionCode = $_SESSION['selected_section_code'] ?? '';

// Get all sections user is a member of for event creation
$availableSections = [];
foreach ($DUMMY_SECTIONS as $section) {
    if (in_array($user['id'], $section['members'])) {
        $availableSections[] = $section;
    }
}

// No month/year parameters needed - we'll show 6 months in vertical scroll

$pageTitle = 'Calendar';
include BASE_PATH . '/includes/header.php';
?>

<style>
    .calendar-page {
        padding-bottom: 80px;
        background: var(--bg-primary);
        min-height: 100vh;
    }
    
    .app-header {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: var(--spacing-lg) var(--spacing-md);
        position: sticky;
        top: 0;
        background: var(--bg-primary);
        z-index: 100;
        border-bottom: 1px solid var(--bg-tertiary);
    }
    
    .app-title {
        font-size: var(--font-size-2xl);
        font-weight: 600;
        color: var(--text-primary);
        letter-spacing: -0.5px;
    }
    
    .calendar-container {
        padding: 0;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .month-section {
        margin-bottom: var(--spacing-2xl);
        padding: 0 var(--spacing-md);
    }
    
    .month-title {
        text-align: center;
        font-size: var(--font-size-xl);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-lg);
        letter-spacing: -0.5px;
        padding: var(--spacing-md) 0;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: var(--spacing-sm) 0;
        margin-bottom: var(--spacing-xl);
    }
    
    .date-cell {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        padding: var(--spacing-xs);
        transition: all 0.2s ease;
    }
    
    .date-cell:hover .date-number {
        transform: scale(1.1);
    }
    
    .date-cell.other-month {
        opacity: 0.3;
    }
    
    .date-cell.other-month .date-number {
        color: var(--text-tertiary);
    }
    
    .date-cell.today .date-number {
        background: rgba(100, 116, 139, 0.5);
        color: var(--text-primary);
    }
    
    .date-number {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-lg);
        color: var(--text-primary);
        font-weight: 400;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .event-indicators {
        display: flex;
        gap: 4px;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
        min-height: 12px;
    }
    
    .event-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--brand-blue);
    }
    
    .event-more {
        font-size: 9px;
        color: var(--text-secondary);
        margin-left: 2px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767px) {
        .app-title {
            font-size: var(--font-size-xl);
        }
        
        .date-number {
            width: 36px;
            height: 36px;
            font-size: var(--font-size-base);
        }
    }
</style>

<div class="page-wrapper calendar-page">
    <div class="app-header">
        <h1 class="app-title">StudyTrack</h1>
    </div>
    
    <?php if ($selectedSectionId): ?>
    <div style="max-width: 600px; margin: 0 auto; padding: 0 var(--spacing-md);">
        <div style="background: rgba(43, 127, 214, 0.1); border: 1px solid rgba(43, 127, 214, 0.3); border-radius: var(--radius-md); padding: var(--spacing-sm) var(--spacing-md); margin-bottom: var(--spacing-md); font-size: var(--font-size-sm); color: var(--brand-blue);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <strong>Section Mode:</strong> Viewing events for <?php echo htmlspecialchars($selectedSectionName); ?> (<?php echo htmlspecialchars($selectedSectionCode); ?>). 
            All events are shared with everyone in this section.
        </div>
    </div>
    <?php endif; ?>
    
    <div class="calendar-container">
        <?php
        // Generate 6 months: 2 previous, current, 3 future
        $currentDate = new DateTime();
        $startDate = (clone $currentDate)->modify('-2 months')->modify('first day of this month');
        $today = date('Y-m-d');
        
        for ($monthIndex = 0; $monthIndex < 6; $monthIndex++) {
            $monthStart = (clone $startDate)->modify("+$monthIndex months");
            $year = (int)$monthStart->format('Y');
            $month = (int)$monthStart->format('n');
            $monthName = $monthStart->format('F Y');
            
            // Get first day of month and number of days
            $firstDay = (int)$monthStart->format('w');
            $daysInMonth = (int)$monthStart->format('t');
            
            // Get events for this month
            $monthEvents = [];
            foreach ($userEvents as $event) {
                $eventDate = new DateTime($event['date']);
                if ((int)$eventDate->format('Y') == $year && (int)$eventDate->format('n') == $month) {
                    $day = (int)$eventDate->format('j');
                    if (!isset($monthEvents[$day])) {
                        $monthEvents[$day] = [];
                    }
                    $monthEvents[$day][] = $event;
                }
            }
            
            echo '<div class="month-section">';
            echo '<div class="month-title">' . $monthName . '</div>';
            echo '<div class="calendar-grid">';
            
            // Previous month's trailing days
            if ($firstDay > 0) {
                $prevMonth = (clone $monthStart)->modify('-1 month');
                $prevMonthDays = (int)$prevMonth->format('t');
                for ($j = $firstDay - 1; $j >= 0; $j--) {
                    $prevDay = $prevMonthDays - $j;
                    echo '<div class="date-cell other-month">';
                    echo '<div class="date-number">' . $prevDay . '</div>';
                    echo '<div class="event-indicators"></div>';
                    echo '</div>';
                }
            }
            
            // Current month days
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = ($dateStr == $today);
                $cssClass = 'date-cell';
                if ($isToday) $cssClass .= ' today';
                
                echo '<div class="' . $cssClass . '" data-date="' . $dateStr . '">';
                echo '<div class="date-number">' . $day . '</div>';
                echo '<div class="event-indicators">';
                
                if (isset($monthEvents[$day])) {
                    $eventCount = count($monthEvents[$day]);
                    $displayCount = min($eventCount, 2);
                    
                    for ($e = 0; $e < $displayCount; $e++) {
                        echo '<div class="event-dot" style="background: ' . htmlspecialchars($monthEvents[$day][$e]['color']) . ';"></div>';
                    }
                    
                    if ($eventCount > 2) {
                        echo '<span class="event-more">+' . ($eventCount - 2) . '</span>';
                    }
                }
                
                echo '</div>';
                echo '</div>';
            }
            
            // Next month's leading days to complete the grid
            $totalCells = $firstDay + $daysInMonth;
            $remainingCells = ($totalCells % 7 == 0) ? 0 : (7 - ($totalCells % 7));
            
            for ($j = 1; $j <= $remainingCells; $j++) {
                echo '<div class="date-cell other-month">';
                echo '<div class="date-number">' . $j . '</div>';
                echo '<div class="event-indicators"></div>';
                echo '</div>';
            }
            
            echo '</div>'; // calendar-grid
            echo '</div>'; // month-section
        }
        ?>
    </div>
</div>

<!-- Day Events Modal -->
<div class="modal-overlay" id="dayModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalDateTitle">Events</h2>
            <button class="close-btn" onclick="closeModal('dayModal')">&times;</button>
        </div>
        <div class="modal-body" id="dayEventsContainer">
            <!-- Events will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('dayModal')">Close</button>
            <button class="btn btn-primary" onclick="openAddEventModal()">Add Event</button>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal-overlay" id="addEventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add Event</h2>
            <button class="close-btn" onclick="closeModal('addEventModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addEventForm" method="POST" action="/pages/calendar.php">
                <input type="hidden" name="create_event" value="1">
                
                <!-- Hidden date field - automatically set when modal opens -->
                <input type="hidden" id="eventDate" name="date" required>
                
                <div class="form-group">
                    <label for="eventType">Event Type</label>
                    <select id="eventType" name="type" required>
                        <option value="">Select event type</option>
                        <option value="notice">Notice</option>
                        <option value="assignment">Assignment</option>
                        <option value="exam">Exam</option>
                        <option value="presentation">Presentation</option>
                        <option value="meeting">Meeting</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="eventSection">Section</label>
                    <select id="eventSection" name="section_id" required>
                        <?php if (empty($availableSections)): ?>
                            <option value="">No sections available</option>
                        <?php else: ?>
                            <?php foreach ($availableSections as $section): ?>
                                <option value="<?php echo $section['id']; ?>" 
                                    <?php echo ($section['id'] == $selectedSectionId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['name'] . ' (' . $section['code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-hint">Events are shared with all members of the selected section</small>
                </div>
                
                <div class="form-group">
                    <label for="eventTitle">Title</label>
                    <input type="text" id="eventTitle" name="title" maxlength="200" placeholder="Event title" required>
                </div>
                
                <div class="form-group">
                    <label for="eventTime">Time (optional)</label>
                    <input type="time" id="eventTime" name="time">
                </div>
                
                <div class="form-group">
                    <label for="eventDetails">Details</label>
                    <textarea id="eventDetails" name="details" maxlength="2000" placeholder="Enter event details..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addEventModal')">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="saveEventAjax()">Save</button>
        </div>
    </div>
</div>

<!-- Include API Client -->
<script src="/assets/js/api-client.js"></script>

<script>
// Enhanced event creation with AJAX
async function saveEventAjax() {
    const form = document.getElementById('addEventForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const eventData = {
        date: formData.get('date'),
        time: formData.get('time') || null,
        type: formData.get('type'),
        title: formData.get('title'),
        details: formData.get('details') || '',
        section_id: parseInt(formData.get('section_id'))
    };
    
    try {
        const spinner = StudyTrackUI.showLoading();
        const response = await StudyTrackAPI.events.create(eventData);
        StudyTrackUI.hideLoading(spinner);
        
        StudyTrackUI.showSuccess('Event created successfully!');
        closeModal('addEventModal');
        form.reset();
        
        // Reload page to show new event
        setTimeout(() => location.reload(), 500);
        
    } catch (error) {
        StudyTrackUI.showError(error.message || 'Failed to create event');
    }
}
</script>

<style>
    .event-card {
        background: var(--bg-tertiary);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-sm);
        border-left: 4px solid;
    }
    
    .event-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--spacing-sm);
    }
    
    .event-title {
        font-weight: 600;
        font-size: var(--font-size-base);
        color: var(--text-primary);
    }
    
    .event-meta {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: 4px;
    }
    
    .event-details {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-top: var(--spacing-sm);
    }
    
    .form-hint {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--brand-blue);
        margin-top: 4px;
        font-style: italic;
    }
</style>

<script>
const userEvents = <?php echo json_encode($userEvents); ?>;
let selectedDate = '';

function openDayModal(date) {
    selectedDate = date;
    const dayEvents = userEvents.filter(e => e.date === date);
    const modalDate = new Date(date + 'T00:00:00');
    const formattedDate = modalDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    document.getElementById('modalDateTitle').textContent = formattedDate;
    
    const container = document.getElementById('dayEventsContainer');
    
    if (dayEvents.length === 0) {
        container.innerHTML = '<p class="text-secondary text-center">No events for this date</p>';
    } else {
        container.innerHTML = dayEvents.map(event => `
            <div class="event-card" style="border-left-color: ${event.color};">
                <div class="event-card-header">
                    <div>
                        <div class="event-title">${escapeHtml(event.title)}</div>
                        <div class="event-meta">
                            ${event.time || 'All day'} • ${event.creator_name}
                            ${event.status === 'pending' ? '<span class="badge badge-pending">Pending</span>' : ''}
                        </div>
                    </div>
                </div>
                ${event.details ? `<div class="event-details">${escapeHtml(event.details)}</div>` : ''}
            </div>
        `).join('');
    }
    
    document.getElementById('dayModal').classList.add('active');
}

function openAddEventModal(date = null) {
    const targetDate = date || selectedDate || new Date().toISOString().split('T')[0];
    document.getElementById('eventDate').value = targetDate;
    document.getElementById('addEventModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Note: saveEvent() function removed - form now submits directly to PHP with POST
// The form has method="POST" and the save button is type="submit"

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
        }
    });
});

// Add click and double-click handlers to date cells
document.querySelectorAll('.date-cell:not(.other-month)').forEach(cell => {
    const dateStr = cell.getAttribute('data-date');
    
    let clickTimer = null;
    
    cell.addEventListener('click', function(e) {
        clearTimeout(clickTimer);
        clickTimer = setTimeout(() => {
            // Single click - view events
            openDayModal(dateStr);
        }, 200);
    });
    
    cell.addEventListener('dblclick', function(e) {
        clearTimeout(clickTimer);
        // Double click - add event
        openAddEventModal(dateStr);
    });
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
