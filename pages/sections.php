<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db_functions.php';

// Require login
requireLogin();

$user = getCurrentUser();
$userSections = getUserSections($user['id']);

// Handle join section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_section'])) {
    $sectionId = (int)$_POST['section_id'];
    try {
        joinSection($sectionId, $user['id']);
        $_SESSION['success_message'] = 'Successfully joined section!';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Failed to join section.';
        error_log("Join section error: " . $e->getMessage());
    }
    header('Location: /pages/sections.php');
    exit;
}

// Handle leave section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_section'])) {
    $sectionId = (int)$_POST['section_id'];
    try {
        leaveSection($sectionId, $user['id']);
        $_SESSION['success_message'] = 'Successfully left section!';
        
        // Clear selected section if user left it
        if ($sectionId == ($_SESSION['selected_section_id'] ?? null)) {
            unset($_SESSION['selected_section_id']);
            unset($_SESSION['selected_section_name']);
            unset($_SESSION['selected_section_code']);
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Failed to leave section.';
        error_log("Leave section error: " . $e->getMessage());
    }
    header('Location: /pages/sections.php');
    exit;
}

// Initialize default section for students
if ($user['role'] === 'student' && !isset($_SESSION['selected_section_id'])) {
    // Set default section 66_B for students
    foreach ($DUMMY_SECTIONS as $section) {
        if ($section['code'] === '66_B' && in_array($user['id'], $section['members'])) {
            $_SESSION['selected_section_id'] = $section['id'];
            $_SESSION['selected_section_name'] = $section['name'];
            $_SESSION['selected_section_code'] = $section['code'];
            break;
        }
    }
}

// Handle section selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_section'])) {
    $sectionId = (int)$_POST['section_id'];
    // Find the section
    foreach ($DUMMY_SECTIONS as $section) {
        if ($section['id'] === $sectionId) {
            $_SESSION['selected_section_id'] = $sectionId;
            $_SESSION['selected_section_name'] = $section['name'];
            $_SESSION['selected_section_code'] = $section['code'];
            
            // For teachers, redirect to calendar
            if ($user['role'] === 'teacher') {
                header('Location: /pages/calendar.php');
                exit;
            } else {
                // For students, just refresh to show new selection
                header('Location: /pages/sections.php');
                exit;
            }
        }
    }
}

// Get all sections with member count
$sectionsWithInfo = [];
foreach ($DUMMY_SECTIONS as $section) {
    $isMember = in_array($user['id'], $section['members']);
    $isCreator = ($section['creator_id'] == $user['id']);
    $memberCount = count($section['members']);
    
    $sectionsWithInfo[] = [
        'section' => $section,
        'is_member' => $isMember,
        'is_creator' => $isCreator,
        'member_count' => $memberCount
    ];
}

$pageTitle = 'Sections';
include BASE_PATH . '/includes/header.php';
?>

<style>
    .sections-page {
        padding-bottom: 100px;
    }
    
    .sections-container {
        max-width: 900px;
        margin: 0 auto;
        padding: var(--spacing-lg);
    }
    
    .page-intro {
        margin-bottom: var(--spacing-xl);
        padding: var(--spacing-md);
        background: var(--bg-tertiary);
        border-radius: var(--radius-md);
        border-left: 3px solid var(--brand-blue);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
        line-height: 1.6;
    }
    
    .page-intro strong {
        color: var(--text-primary);
        display: block;
        margin-bottom: 4px;
    }
    
    .section-card {
        background: var(--bg-secondary);
        border: 1px solid var(--bg-tertiary);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        transition: all 0.2s ease;
    }
    
    .section-card:hover {
        border-color: rgba(43, 127, 214, 0.5);
    }
    
    .section-card.selected {
        border-color: var(--brand-blue);
        background: rgba(43, 127, 214, 0.1);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: var(--spacing-md);
    }
    
    .section-info {
        flex: 1;
    }
    
    .section-name {
        font-size: var(--font-size-lg);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .section-code {
        font-size: var(--font-size-sm);
        color: var(--brand-blue);
        font-family: 'Courier New', monospace;
        margin-bottom: var(--spacing-sm);
    }
    
    .section-meta {
        display: flex;
        gap: var(--spacing-md);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-sm);
    }
    
    .section-description {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-md);
    }
    
    .section-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .member-count {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }
    
    .section-actions {
        display: flex;
        gap: var(--spacing-sm);
        align-items: center;
        flex-wrap: wrap;
    }
    
    .btn-sm {
        padding: 8px 16px;
        font-size: var(--font-size-sm);
    }
    
    .tabs {
        display: flex;
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-xl);
        border-bottom: 1px solid var(--bg-tertiary);
    }
    
    .tab {
        padding: var(--spacing-md) var(--spacing-lg);
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: var(--font-size-base);
        font-weight: 600;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
    }
    
    .tab:hover {
        color: var(--text-primary);
    }
    
    .tab.active {
        color: var(--brand-blue);
        border-bottom-color: var(--brand-blue);
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
    
    .search-box {
        position: relative;
        display: flex;
        gap: var(--spacing-sm);
    }
    
    .search-input {
        flex: 1;
        padding: var(--spacing-md) var(--spacing-lg);
        background: var(--bg-secondary);
        border: 1px solid var(--bg-tertiary);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: var(--font-size-base);
        transition: all 0.2s ease;
    }
    
    .search-input:focus {
        outline: none;
        border-color: var(--brand-blue);
        background: var(--bg-tertiary);
    }
    
    .search-input::placeholder {
        color: var(--text-tertiary);
    }
    
    .search-btn {
        padding: var(--spacing-md) var(--spacing-xl);
        background: var(--brand-blue);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: var(--font-size-base);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .search-btn:hover {
        background: var(--brand-blue-hover);
        transform: translateY(-1px);
    }
    
    .search-btn:active {
        transform: translateY(0);
    }
    
    .highlight {
        background-color: rgba(43, 127, 214, 0.3);
        color: var(--brand-blue);
        font-weight: 600;
        padding: 2px 0;
        border-radius: 2px;
    }
</style>

<div class="page-wrapper sections-page">
    <div class="page-header">
        <h1>Sections</h1>
    </div>
    
    <div class="sections-container">
        <div class="page-intro">
            <?php if ($user['role'] === 'teacher'): ?>
                <strong>All Sections:</strong> Browse and join any section.<br>
                <strong>My Sections:</strong> View and select from your joined sections.
            <?php endif; ?>
        </div>
        
        <?php if ($user['role'] === 'teacher'): ?>
        <div class="search-box" style="margin-bottom: var(--spacing-lg);">
            <input type="text" id="searchInput" class="search-input" placeholder="Search sections by name or code..." onkeypress="if(event.key==='Enter') searchSections()">
            <button class="search-btn" onclick="searchSections()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                Search
            </button>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('all')">All Sections</button>
            <button class="tab" onclick="showTab('joined')">My Sections</button>
        </div>
        <?php endif; ?>
        
        <div id="allSections">
            <?php if (empty($sectionsWithInfo)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <div class="empty-text">No sections available</div>
                </div>
            <?php else: ?>
                <?php 
                // For students, only show 66_B section
                $sectionsToShow = $sectionsWithInfo;
                if ($user['role'] === 'student') {
                    $sectionsToShow = array_filter($sectionsWithInfo, function($item) {
                        return $item['section']['code'] === '66_B';
                    });
                }
                ?>
                <?php foreach ($sectionsToShow as $item): ?>
                    <?php 
                    $section = $item['section']; 
                    $isSelected = isset($_SESSION['selected_section_id']) && $_SESSION['selected_section_id'] == $section['id'];
                    ?>
                    <div class="section-card <?php echo $isSelected ? 'selected' : ''; ?>" 
                         data-joined="<?php echo $item['is_member'] ? '1' : '0'; ?>" 
                         data-created="<?php echo $item['is_creator'] ? '1' : '0'; ?>"
                         data-section-tab="<?php echo $item['is_member'] ? 'my' : 'all'; ?>"
                         id="section-<?php echo $section['id']; ?>">
                        <div class="section-header">
                            <div class="section-info">
                                <div class="section-name"><?php echo htmlspecialchars($section['name']); ?></div>
                                <div class="section-code"><?php echo htmlspecialchars($section['code']); ?></div>
                                <div class="section-meta">
                                    <span><?php echo $section['academic_year']; ?></span>
                                    <span>•</span>
                                    <span><?php echo $section['semester']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($section['description']): ?>
                        <div class="section-description">
                            <?php echo htmlspecialchars($section['description']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="section-footer">
                            <div class="member-count">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <?php echo $item['member_count']; ?> members
                            </div>
                            
                            <div class="section-actions">
                                <?php if ($item['is_member']): ?>
                                    <!-- In My Sections: Show joined badge and select button -->
                                    <?php if ($isSelected): ?>
                                        <span class="badge badge-info">Active</span>
                                    <?php endif; ?>
                                    <span class="badge badge-approved">Enrolled</span>
                                    <?php if ($user['role'] === 'teacher'): ?>
                                        <button class="btn btn-primary btn-sm my-section-action" onclick="selectSection(<?php echo $section['id']; ?>)">Select</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- In All Sections: Show join button for unjoined sections -->
                                    <?php if ($user['role'] === 'teacher'): ?>
                                        <button class="btn btn-primary btn-sm all-section-action" onclick="joinSection(<?php echo $section['id']; ?>)">Join</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let currentTab = '<?php echo $user['role'] === 'student' ? 'joined' : 'all'; ?>';
let originalContent = new Map();

function showTab(tab) {
    currentTab = tab;
    // Remove active class from all tabs
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    
    // Add active class to clicked tab
    event.target.classList.add('active');
    
    // Apply filters
    filterSections();
}

function highlightText(text, searchTerm) {
    if (!searchTerm) return text;
    
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<span class="highlight">$1</span>');
}

function filterSections() {
    const searchTerm = document.getElementById('searchInput')?.value.trim() || '';
    const cards = document.querySelectorAll('.section-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const nameEl = card.querySelector('.section-name');
        const codeEl = card.querySelector('.section-code');
        
        // Store original content if not already stored
        if (!originalContent.has(card)) {
            originalContent.set(card, {
                name: nameEl.textContent,
                code: codeEl.textContent
            });
        }
        
        const original = originalContent.get(card);
        const sectionName = original.name.toLowerCase();
        const sectionCode = original.code.toLowerCase();
        const searchLower = searchTerm.toLowerCase();
        
        const matchesSearch = sectionName.includes(searchLower) || sectionCode.includes(searchLower);
        
        // Determine if card matches the current tab
        let matchesTab = false;
        if (currentTab === 'all') {
            // All sections tab: show all sections
            matchesTab = true;
        } else if (currentTab === 'joined') {
            // My sections tab: show only joined sections
            matchesTab = card.dataset.joined === '1';
        }
        
        if (matchesSearch && matchesTab) {
            card.style.display = 'block';
            visibleCount++;
            
            // Show/hide appropriate buttons based on tab
            const allSectionActions = card.querySelectorAll('.all-section-action');
            const mySectionActions = card.querySelectorAll('.my-section-action');
            
            if (currentTab === 'all') {
                // In All Sections: show join button for unjoined, hide select/leave buttons
                allSectionActions.forEach(btn => btn.style.display = 'inline-flex');
                mySectionActions.forEach(btn => btn.style.display = 'none');
            } else if (currentTab === 'joined') {
                // In My Sections: show select/leave button, hide join button
                allSectionActions.forEach(btn => btn.style.display = 'none');
                mySectionActions.forEach(btn => btn.style.display = 'inline-flex');
            }
            
            // Apply highlighting if there's a search term
            if (searchTerm) {
                nameEl.innerHTML = highlightText(original.name, searchTerm);
                codeEl.innerHTML = highlightText(original.code, searchTerm);
            } else {
                nameEl.textContent = original.name;
                codeEl.textContent = original.code;
            }
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show empty state if no results
    const existingEmpty = document.querySelector('.search-empty-state');
    if (existingEmpty) existingEmpty.remove();
    
    if (visibleCount === 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state search-empty-state';
        if (searchTerm) {
            emptyState.innerHTML = `
                <div class="empty-icon">🔍</div>
                <div class="empty-text">No sections found matching "${searchTerm}"</div>
            `;
        } else if (currentTab === 'joined') {
            emptyState.innerHTML = `
                <div class="empty-icon">📚</div>
                <div class="empty-text">You haven't joined any sections yet</div>
            `;
        } else {
            emptyState.innerHTML = `
                <div class="empty-icon">📚</div>
                <div class="empty-text">No sections available</div>
            `;
        }
        document.getElementById('allSections').appendChild(emptyState);
    }
}

function searchSections() {
    filterSections();
}

function selectSection(sectionId) {
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    const input1 = document.createElement('input');
    input1.type = 'hidden';
    input1.name = 'select_section';
    input1.value = '1';
    
    const input2 = document.createElement('input');
    input2.type = 'hidden';
    input2.name = 'section_id';
    input2.value = sectionId;
    
    form.appendChild(input1);
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
}

<!-- Include API Client -->
<script src="/assets/js/api-client.js"></script>

<script>
// Enhanced join/leave with AJAX
async function joinSectionAjax(sectionId) {
    if (!await StudyTrackUI.confirm('Are you sure you want to join this section?')) {
        return;
    }
    
    try {
        const spinner = StudyTrackUI.showLoading();
        const response = await StudyTrackAPI.sections.join(sectionId);
        StudyTrackUI.hideLoading(spinner);
        
        StudyTrackUI.showSuccess(response.message);
        setTimeout(() => location.reload(), 500);
        
    } catch (error) {
        StudyTrackUI.showError(error.message || 'Failed to join section');
    }
}

async function leaveSectionAjax(sectionId) {
    if (!await StudyTrackUI.confirm('Are you sure you want to leave this section?')) {
        return;
    }
    
    try {
        const spinner = StudyTrackUI.showLoading();
        const response = await StudyTrackAPI.sections.leave(sectionId);
        StudyTrackUI.hideLoading(spinner);
        
        StudyTrackUI.showSuccess(response.message);
        setTimeout(() => location.reload(), 500);
        
    } catch (error) {
        StudyTrackUI.showError(error.message || 'Failed to leave section');
    }
}

// Keep backward compatibility with form-based approach
function joinSection(sectionId) {
    // Use AJAX if API client is available
    if (window.StudyTrackAPI) {
        joinSectionAjax(sectionId);
        return;
    }
    
    // Fallback to form submission
    if (confirm('Are you sure you want to join this section?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'join_section';
        input1.value = '1';
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'section_id';
        input2.value = sectionId;
        
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}

function leaveSection(sectionId) {
    // Use AJAX if API client is available
    if (window.StudyTrackAPI) {
        leaveSectionAjax(sectionId);
        return;
    }
    
    // Fallback to form submission
    if (confirm('Are you sure you want to leave this section?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'leave_section';
        input1.value = '1';
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'section_id';
        input2.value = sectionId;
        
        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

function requestJoinSection(sectionId) {
    alert('Request has been sent! Please wait for approval from the teacher.');
}

// Initialize filtering on page load
document.addEventListener('DOMContentLoaded', function() {
    filterSections();
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
