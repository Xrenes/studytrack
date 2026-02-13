/* ============================================
   StudyTrack - Profile JavaScript
   User Profile & Settings
   ============================================ */

// === LOAD PROFILE DATA ===

function loadProfile() {
    const user = window.StudyTrackAuth.getCurrentUser();
    
    if (!user) {
        window.location.href = 'login.html';
        return;
    }

    // Profile name and info
    document.getElementById('profileName').textContent = user.full_name;
    document.getElementById('profileId').textContent = user.student_id || 'No ID';
    document.getElementById('profileEmail').textContent = user.email;
    document.getElementById('profileRole').textContent = user.role.toUpperCase();

    // Profile photo (initials)
    const initials = user.full_name.split(' ').map(n => n[0]).join('').toUpperCase();
    document.getElementById('profilePhoto').textContent = initials;

    // Load statistics
    loadStatistics(user);
}

// === LOAD STATISTICS ===

function loadStatistics(user) {
    const events = JSON.parse(localStorage.getItem('events')) || [];
    
    // Filter events for current user
    const userEvents = events.filter(event => {
        if (user.role === 'personal') {
            return event.creator === user.full_name;
        }
        return event.status === 'approved' || event.creator === user.full_name;
    });

    // Total events
    document.getElementById('totalEvents').textContent = userEvents.length;

    // Upcoming events
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const upcoming = userEvents.filter(event => {
        const eventDate = new Date(event.date);
        return eventDate >= today;
    });
    document.getElementById('upcomingEvents').textContent = upcoming.length;
}

// === THEME TOGGLE ===

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update toggle switch
    const themeSwitch = document.getElementById('themeSwitch');
    if (newTheme === 'light') {
        themeSwitch.classList.add('active');
    } else {
        themeSwitch.classList.remove('active');
    }

    window.StudyTrackAuth.showSuccess(`Switched to ${newTheme} mode`);
}

// === LOGOUT ===

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.StudyTrackAuth.logout();
    }
}

// === INITIALIZE ===

document.addEventListener('DOMContentLoaded', () => {
    loadProfile();

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    const themeSwitch = document.getElementById('themeSwitch');
    if (savedTheme === 'light') {
        themeSwitch.classList.add('active');
    }
});

// === EXPORT ===
window.ProfileApp = {
    loadProfile,
    toggleTheme,
    logout
};
