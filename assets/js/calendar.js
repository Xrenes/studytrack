/* ============================================
   StudyTrack - Calendar JavaScript
   Calendar Rendering & Event Management
   ============================================ */

// === DUMMY EVENTS DATA ===
let dummyEvents = [
    {
        id: 1,
        date: '2026-07-07',
        time: '10:00',
        type: 'exam',
        title: 'Mid Exam',
        details: 'Chapters 1-5 coverage',
        color: '#8B5CF6',
        creator: 'Prof. Sarah Wilson',
        status: 'approved',
        section: 'CSE 101'
    },
    {
        id: 2,
        date: '2026-07-16',
        time: null,
        type: 'assignment',
        title: 'Assignment 1',
        details: 'Complete all exercises from textbook',
        color: '#3B82F6',
        creator: 'John Doe',
        status: 'pending',
        section: 'CSE 101'
    },
    {
        id: 3,
        date: '2026-07-22',
        time: '14:00',
        type: 'exam',
        title: 'Final Exam',
        details: 'Comprehensive final examination',
        color: '#8B5CF6',
        creator: 'Prof. Sarah Wilson',
        status: 'approved',
        section: 'CSE 101'
    },
    {
        id: 4,
        date: '2026-07-10',
        time: null,
        type: 'presentation',
        title: 'Project Presentation',
        details: 'Group project presentation',
        color: '#10B981',
        creator: 'Jane Smith',
        status: 'approved',
        section: 'CSE 101'
    },
    {
        id: 5,
        date: '2026-08-05',
        time: '09:00',
        type: 'meeting',
        title: 'Team Meeting',
        details: 'Discuss project progress',
        color: '#F59E0B',
        creator: 'John Doe',
        status: 'approved',
        section: null
    }
];

// Store events in localStorage if not exists
if (!localStorage.getItem('events')) {
    localStorage.setItem('events', JSON.stringify(dummyEvents));
}

// === CALENDAR STATE ===
let currentDate = new Date();
let selectedDate = null;

// === UTILITY FUNCTIONS ===

function getMonthName(month) {
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
    return months[month];
}

function getMonthDays(year, month) {
    return new Date(year, month + 1, 0).getDate();
}

function getFirstDayOfMonth(year, month) {
    return new Date(year, month, 1).getDay();
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseDate(dateString) {
    const [year, month, day] = dateString.split('-');
    return new Date(year, month - 1, day);
}

function formatDateDisplay(dateString) {
    const date = parseDate(dateString);
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayName = days[date.getDay()];
    const monthName = getMonthName(date.getMonth());
    return `${dayName}, ${monthName} ${date.getDate()}`;
}

function isToday(date) {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
}

// === EVENT FUNCTIONS ===

function getEvents() {
    return JSON.parse(localStorage.getItem('events')) || [];
}

function getEventsByDate(dateString) {
    const events = getEvents();
    const user = window.StudyTrackAuth.getCurrentUser();
    
    return events.filter(event => {
        if (event.date !== dateString) return false;
        
        // Show own events and approved events
        if (user.role === 'personal') {
            return event.creator === user.full_name;
        }
        
        return event.status === 'approved' || event.creator === user.full_name;
    });
}

function addEvent(eventData) {
    const events = getEvents();
    const newEvent = {
        id: Date.now(),
        ...eventData,
        creator: window.StudyTrackAuth.getCurrentUser().full_name,
        status: window.StudyTrackAuth.getCurrentUser().role === 'teacher' ? 'approved' : 'pending'
    };
    events.push(newEvent);
    localStorage.setItem('events', JSON.stringify(events));
    return newEvent;
}

function updateEvent(id, updates) {
    const events = getEvents();
    const index = events.findIndex(e => e.id === id);
    if (index !== -1) {
        events[index] = { ...events[index], ...updates };
        localStorage.setItem('events', JSON.stringify(events));
        return events[index];
    }
    return null;
}

function deleteEvent(id) {
    const events = getEvents();
    const filtered = events.filter(e => e.id !== id);
    localStorage.setItem('events', JSON.stringify(filtered));
}

// === CALENDAR RENDERING ===

function renderCalendar() {
    const container = document.getElementById('calendarContainer');
    if (!container) return;

    container.innerHTML = '';

    // Render current month and next 2 months
    for (let i = 0; i < 3; i++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() + i, 1);
        renderMonth(container, date.getFullYear(), date.getMonth());
    }
}

function renderMonth(container, year, month) {
    const monthSection = document.createElement('div');
    monthSection.className = 'month-section';
    
    // Month label
    const monthLabel = document.createElement('h2');
    monthLabel.className = 'month-label';
    monthLabel.textContent = `${getMonthName(month)} ${year}`;
    monthSection.appendChild(monthLabel);

    // Weekday headers (desktop only)
    const weekdayHeader = document.createElement('div');
    weekdayHeader.className = 'weekday-header';
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdays.forEach(day => {
        const span = document.createElement('span');
        span.textContent = day;
        weekdayHeader.appendChild(span);
    });
    monthSection.appendChild(weekdayHeader);

    // Calendar grid
    const grid = document.createElement('div');
    grid.className = 'calendar-grid';

    const firstDay = getFirstDayOfMonth(year, month);
    const monthDays = getMonthDays(year, month);

    // Empty cells for days before month starts (desktop only)
    for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'date-cell empty';
        grid.appendChild(emptyCell);
    }

    // Date cells
    for (let day = 1; day <= monthDays; day++) {
        const date = new Date(year, month, day);
        const dateString = formatDate(date);
        const dayEvents = getEventsByDate(dateString);

        const cell = document.createElement('div');
        cell.className = 'date-cell';
        if (isToday(date)) cell.classList.add('today');
        if (dayEvents.length > 0) cell.classList.add('has-events');

        // Date number
        const dateNumber = document.createElement('div');
        dateNumber.className = 'date-number';
        dateNumber.textContent = day;
        cell.appendChild(dateNumber);

        // Event indicators
        if (dayEvents.length > 0) {
            const indicators = document.createElement('div');
            indicators.className = 'event-indicators';

            // Show up to 3 dots
            dayEvents.slice(0, 3).forEach(event => {
                const dot = document.createElement('span');
                dot.className = `event-dot ${event.type}`;
                indicators.appendChild(dot);
            });

            if (dayEvents.length > 3) {
                const count = document.createElement('span');
                count.className = 'event-count';
                count.textContent = `+${dayEvents.length - 3}`;
                indicators.appendChild(count);
            }

            cell.appendChild(indicators);
        }

        // Click handler
        cell.addEventListener('click', () => {
            selectedDate = dateString;
            openDayModal(dateString, dayEvents);
        });

        grid.appendChild(cell);
    }

    monthSection.appendChild(grid);
    container.appendChild(monthSection);
}

// === DAY MODAL ===

function openDayModal(dateString, events) {
    const modal = document.getElementById('dayModal');
    const modalDate = document.getElementById('modalDate');
    const eventsList = document.getElementById('modalEventsList');

    modalDate.textContent = formatDateDisplay(dateString);
    eventsList.innerHTML = '';

    if (events.length === 0) {
        eventsList.innerHTML = `
            <div style="text-align: center; padding: var(--spacing-xl); color: var(--text-tertiary);">
                <p>No events on this date</p>
                <button class="btn btn-primary mt-md" onclick="openAddEventModal('${dateString}')">
                    Add Event
                </button>
            </div>
        `;
    } else {
        events.forEach(event => {
            const eventCard = createEventCard(event);
            eventsList.appendChild(eventCard);
        });

        const addBtn = document.createElement('button');
        addBtn.className = 'btn btn-primary btn-full mt-md';
        addBtn.textContent = 'Add Another Event';
        addBtn.onclick = () => openAddEventModal(dateString);
        eventsList.appendChild(addBtn);
    }

    modal.classList.add('show');
}

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-details';
    card.innerHTML = `
        <div class="event-details-header">
            <span class="event-type-badge ${event.type}">${event.type}</span>
            ${event.status === 'pending' ? '<span class="badge badge-pending">Pending Approval</span>' : ''}
        </div>
        <h3 style="margin-bottom: var(--spacing-sm);">${event.title}</h3>
        ${event.time ? `<p style="color: var(--text-tertiary); font-size: var(--font-size-sm); margin-bottom: var(--spacing-sm);">⏰ ${event.time}</p>` : ''}
        <p style="color: var(--text-secondary); font-size: var(--font-size-sm); margin-bottom: var(--spacing-sm);">👤 ${event.creator}</p>
        ${event.section ? `<p style="color: var(--text-tertiary); font-size: var(--font-size-sm); margin-bottom: var(--spacing-sm);">📚 ${event.section}</p>` : ''}
        <div class="event-details-content">${event.details || 'No additional details'}</div>
        <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-md);">
            <button class="btn btn-secondary" style="flex: 1;" onclick="editEvent(${event.id})">Edit</button>
            <button class="btn btn-error" style="flex: 1;" onclick="deleteEventConfirm(${event.id})">Delete</button>
        </div>
    `;
    return card;
}

// === ADD/EDIT EVENT MODAL ===

function openAddEventModal(dateString = null) {
    const modal = document.getElementById('addEventModal');
    const form = document.getElementById('addEventForm');
    const dateInput = document.getElementById('eventDate');

    form.reset();
    
    if (dateString) {
        dateInput.value = dateString;
    } else {
        dateInput.value = formatDate(new Date());
    }

    modal.classList.add('show');
    closeDayModal();
}

function submitAddEvent(event) {
    event.preventDefault();

    const form = event.target;
    const eventData = {
        date: form.eventDate.value,
        time: form.eventTime.value || null,
        type: form.eventType.value,
        title: form.eventTitle.value,
        details: form.eventDetails.value,
        color: getEventColor(form.eventType.value),
        section: form.eventSection?.value || null
    };

    addEvent(eventData);
    closeAddEventModal();
    renderCalendar();
    
    window.StudyTrackAuth.showSuccess('Event created successfully!');
}

function getEventColor(type) {
    const colors = {
        notice: '#F97316',
        assignment: '#3B82F6',
        exam: '#8B5CF6',
        presentation: '#10B981',
        meeting: '#F59E0B',
        other: '#6B7280'
    };
    return colors[type] || colors.other;
}

function editEvent(id) {
    // Implementation for edit functionality
    window.StudyTrackAuth.showSuccess('Edit functionality coming soon!');
}

function deleteEventConfirm(id) {
    if (confirm('Are you sure you want to delete this event?')) {
        deleteEvent(id);
        closeDayModal();
        renderCalendar();
        window.StudyTrackAuth.showSuccess('Event deleted successfully');
    }
}

// === MODAL CONTROLS ===

function closeDayModal() {
    document.getElementById('dayModal')?.classList.remove('show');
}

function closeAddEventModal() {
    document.getElementById('addEventModal')?.classList.remove('show');
}

// === INITIALIZE ===

document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();

    // Add event button
    const addBtn = document.getElementById('addEventBtn');
    if (addBtn) {
        addBtn.addEventListener('click', () => openAddEventModal());
    }

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Close buttons
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal-overlay').classList.remove('show');
        });
    });

    // Add event form
    const addEventForm = document.getElementById('addEventForm');
    if (addEventForm) {
        addEventForm.addEventListener('submit', submitAddEvent);
    }
});

// === EXPORT ===
window.CalendarApp = {
    openAddEventModal,
    closeDayModal,
    closeAddEventModal,
    renderCalendar
};
