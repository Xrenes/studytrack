/* ============================================
   StudyTrack - Tasks JavaScript  
   Tasks List & Filtering
   ============================================ */

let currentFilter = 'all';
let searchQuery = '';

// === UTILITY FUNCTIONS ===

function getEvents() {
    return JSON.parse(localStorage.getItem('events')) || [];
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const month = date.getMonth() + 1;
    const day = date.getDate();
    const year = date.getFullYear();
    return `${month}/${day}/${year}`;
}

function isUpcoming(dateString) {
    const eventDate = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return eventDate >= today;
}

function getEventIcon(type) {
    const icons = {
        notice: '📢',
        assignment: '📝',
        exam: '📚',
        presentation: '🎤',
        meeting: '👥',
        other: '📌'
    };
    return icons[type] || icons.other;
}

// === FILTER & SEARCH ===

function filterEvents(events) {
    const user = window.StudyTrackAuth.getCurrentUser();
    
    let filtered = events.filter(event => {
        // User visibility filter
        if (user.role === 'personal') {
            if (event.creator !== user.full_name) return false;
        } else {
            if (event.status !== 'approved' && event.creator !== user.full_name) {
                return false;
            }
        }

        // Filter by type
        switch (currentFilter) {
            case 'upcoming':
                return isUpcoming(event.date);
            case 'pending':
                return event.status === 'pending';
            case 'completed':
                return event.is_completed || false;
            case 'all':
            default:
                return true;
        }
    });

    // Search filter
    if (searchQuery) {
        filtered = filtered.filter(event =>
            event.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
            event.details?.toLowerCase().includes(searchQuery.toLowerCase())
        );
    }

    // Sort by date (ascending)
    filtered.sort((a, b) => new Date(a.date) - new Date(b.date));

    return filtered;
}

// === RENDER TASKS ===

function renderTasks() {
    const container = document.getElementById('tasksList');
    const events = getEvents();
    const filtered = filterEvents(events);

    container.innerHTML = '';

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="tasks-empty">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3>No tasks found</h3>
                <p>${searchQuery ? 'Try a different search term' : 'Add some events to get started'}</p>
            </div>
        `;
        return;
    }

    filtered.forEach(event => {
        const card = createTaskCard(event);
        container.appendChild(card);
    });
}

function createTaskCard(event) {
    const card = document.createElement('div');
    card.className = 'task-card';
    if (event.is_completed) card.classList.add('completed');

    // Icon
    const icon = document.createElement('div');
    icon.className = `task-icon ${event.type}`;
    icon.textContent = getEventIcon(event.type);
    card.appendChild(icon);

    // Content
    const content = document.createElement('div');
    content.className = 'task-content';
    
    const title = document.createElement('div');
    title.className = 'task-title';
    title.textContent = event.title;
    content.appendChild(title);

    const subtitle = document.createElement('div');
    subtitle.className = 'task-subtitle';
    subtitle.textContent = event.creator;
    if (event.section) {
        subtitle.textContent += ` • ${event.section}`;
    }
    content.appendChild(subtitle);

    card.appendChild(content);

    // Meta
    const meta = document.createElement('div');
    meta.className = 'task-meta';

    const date = document.createElement('div');
    date.className = 'task-date';
    date.textContent = formatDate(event.date);
    meta.appendChild(date);

    if (event.status === 'pending') {
        const badge = document.createElement('span');
        badge.className = 'task-status-badge pending';
        badge.textContent = 'Pending';
        meta.appendChild(badge);
    } else if (event.is_completed) {
        const badge = document.createElement('span');
        badge.className = 'task-status-badge completed';
        badge.textContent = 'Completed';
        meta.appendChild(badge);
    }

    card.appendChild(meta);

    // Click handler
    card.addEventListener('click', () => openTaskDetail(event));

    return card;
}

// === TASK DETAIL MODAL ===

function openTaskDetail(event) {
    const modal = document.getElementById('taskDetailModal');
    const title = document.getElementById('taskDetailTitle');
    const body = document.getElementById('taskDetailBody');

    title.textContent = event.title;
    
    body.innerHTML = `
        <div class="event-details">
            <div class="event-details-header">
                <span class="event-type-badge ${event.type}">${event.type}</span>
                ${event.status === 'pending' ? '<span class="badge badge-pending">Pending Approval</span>' : ''}
                ${event.status === 'approved' ? '<span class="badge badge-success">Approved</span>' : ''}
            </div>
            
            <div style="margin: var(--spacing-md) 0;">
                <p style="color: var(--text-tertiary); font-size: var(--font-size-sm); margin-bottom: var(--spacing-xs);">
                    📅 ${formatDate(event.date)}${event.time ? ` at ${event.time}` : ''}
                </p>
                <p style="color: var(--text-secondary); font-size: var(--font-size-sm); margin-bottom: var(--spacing-xs);">
                    👤 Created by ${event.creator}
                </p>
                ${event.section ? `<p style="color: var(--text-tertiary); font-size: var(--font-size-sm);">📚 ${event.section}</p>` : ''}
            </div>

            <div class="event-details-content" style="margin: var(--spacing-lg) 0;">
                <h4 style="margin-bottom: var(--spacing-sm); color: var(--text-secondary);">Details:</h4>
                <p>${event.details || 'No additional details provided'}</p>
            </div>

            <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
                <button class="btn btn-secondary" style="flex: 1;" onclick="editTask(${event.id})">Edit</button>
                <button class="btn btn-error" style="flex: 1;" onclick="deleteTask(${event.id})">Delete</button>
            </div>
        </div>
    `;

    modal.classList.add('show');
}

function closeModal() {
    document.getElementById('taskDetailModal')?.classList.remove('show');
}

function editTask(id) {
    window.StudyTrackAuth.showSuccess('Edit functionality coming soon!');
    closeModal();
}

function deleteTask(id) {
    if (confirm('Are you sure you want to delete this event?')) {
        const events = getEvents();
        const filtered = events.filter(e => e.id !== id);
        localStorage.setItem('events', JSON.stringify(filtered));
        closeModal();
        renderTasks();
        window.StudyTrackAuth.showSuccess('Event deleted successfully');
    }
}

// === EVENT LISTENERS ===

document.addEventListener('DOMContentLoaded', () => {
    renderTasks();

    // Filter buttons
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            renderTasks();
        });
    });

    // Search input
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        searchQuery = e.target.value;
        renderTasks();
    });

    // Close modal on outside click
    const modal = document.getElementById('taskDetailModal');
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
});

// === EXPORT ===
window.TasksApp = {
    closeModal,
    renderTasks
};
