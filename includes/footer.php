
<!-- Bottom Navigation (for logged-in users) -->
<?php if (isLoggedIn()): ?>
<nav class="bottom-nav">
    <a href="/pages/calendar.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span>Calendar</span>
    </a>
    <a href="/pages/tasks.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 11 12 14 22 4"></polyline>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
        </svg>
        <span>Tasks</span>
    </a>
    <a href="/pages/profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span>Profile</span>
    </a>
</nav>

<style>
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        background: var(--bg-secondary);
        border-top: 1px solid var(--bg-tertiary);
        display: flex;
        justify-content: space-around;
        padding: var(--spacing-sm) 0;
        z-index: 1000;
        width: 100%;
        max-width: 600px;
    }
    
    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        color: var(--text-secondary);
        text-decoration: none;
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        flex: 1;
        max-width: 120px;
    }
    
    .nav-item:hover {
        color: var(--text-primary);
        background: var(--bg-tertiary);
    }
    
    .nav-item.active {
        color: var(--brand-blue);
    }
    
    .nav-item svg {
        width: 24px;
        height: 24px;
    }
    
    .nav-item span {
        font-size: var(--font-size-xs);
        font-weight: 500;
    }
</style>
<?php endif; ?>

</body>
</html>
