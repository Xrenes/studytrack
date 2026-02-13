<?php
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/db_functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /pages/calendar.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all fields';
    } else {
        // Query database for user
        $user = getUserByEmail($email, $role);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['student_id'] = $user['student_id'];
            
            header('Location: /pages/calendar.php');
            exit;
        } else {
            $error = 'Invalid email, password, or account type';
        }
    }
}

$pageTitle = 'Login';
include BASE_PATH . '/includes/header.php';
?>

<style>
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--spacing-md);
        background: var(--bg-primary);
    }
    
    .auth-card {
        background: var(--bg-secondary);
        border-radius: var(--radius-xl);
        padding: var(--spacing-2xl);
        max-width: 450px;
        width: 100%;
        box-shadow: var(--shadow-lg);
    }
    
    .logo-container {
        text-align: center;
        margin-bottom: var(--spacing-2xl);
    }
    
    .logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-lg);
    }
    
    .logo-icon {
        width: auto;
        max-width: 400px;
        height: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .logo-icon img {
        width: 100%;
        height: auto;
        object-fit: contain;
    }
    
    .password-toggle {
        position: relative;
    }
    
    .password-toggle input {
        padding-right: 45px;
    }
    
    .toggle-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
    }
    
    .toggle-btn:hover {
        color: var(--text-primary);
    }
    
    .account-type-selector {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
    }
    
    .account-type-btn {
        padding: 12px;
        background: var(--bg-tertiary);
        border: 2px solid transparent;
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-size: var(--font-size-base);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .account-type-btn:hover {
        border-color: var(--text-tertiary);
    }
    
    .account-type-btn.active {
        border-color: var(--brand-blue);
        background: rgba(43, 127, 214, 0.1);
        color: var(--brand-blue);
    }
    
    .divider {
        display: flex;
        align-items: center;
        margin: var(--spacing-xl) 0;
        color: var(--text-tertiary);
        font-size: var(--font-size-sm);
    }
    
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--text-tertiary);
    }
    
    .divider span {
        padding: 0 var(--spacing-md);
    }
    
    .google-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-md);
        width: 100%;
        padding: 12px;
        background: var(--bg-tertiary);
        border: 1px solid var(--text-tertiary);
        border-radius: var(--radius-sm);
        color: var(--text-secondary);
        font-size: var(--font-size-base);
        font-weight: 600;
        cursor: not-allowed;
        opacity: 0.5;
        margin-bottom: var(--spacing-sm);
        position: relative;
    }
    
    .google-btn .tooltip {
        position: absolute;
        top: -35px;
        background: var(--bg-primary);
        padding: 6px 12px;
        border-radius: var(--radius-sm);
        font-size: var(--font-size-xs);
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    
    .google-btn:hover .tooltip {
        opacity: 1;
    }
    
    .alert {
        padding: 12px var(--spacing-md);
        border-radius: var(--radius-sm);
        margin-bottom: var(--spacing-lg);
        font-size: var(--font-size-sm);
    }
    
    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid var(--error);
        color: var(--error);
    }
    
    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid var(--success);
        color: var(--success);
    }
    
    .helper-text {
        text-align: center;
        margin-top: var(--spacing-lg);
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }
    
    .helper-text a {
        color: var(--brand-blue);
        text-decoration: none;
        font-weight: 600;
    }
    
    .helper-text a:hover {
        text-decoration: underline;
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="logo-container">
            <div class="logo">
                <div class="logo-icon">
                    <img src="../assets/images/22.png" alt="StudyTrack Logo">
                </div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your.name@diu.edu.bd" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label>Account Type</label>
                <div class="account-type-selector">
                    <button type="button" class="account-type-btn active" data-role="student" onclick="selectRole('student')">
                        Student
                    </button>
                    <button type="button" class="account-type-btn" data-role="teacher" onclick="selectRole('teacher')">
                        Teacher
                    </button>
                </div>
                <input type="hidden" name="role" id="roleInput" value="student">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Sign In</button>
        </form>
        
        <div class="divider">
            <span>OR CONTINUE WITH</span>
        </div>
        
        <button class="google-btn">
            <span class="tooltip">Coming Soon</span>
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </button>
        
        <button class="google-btn">
            <span class="tooltip">Coming Soon</span>
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Sign Up with Google
        </button>
        
        <div class="helper-text">
            Don't have an account? <a href="/auth/register.php">Sign up</a>
        </div>
        
        <div class="helper-text mt-md" style="font-size: 11px; color: var(--text-tertiary);">
            <strong>Demo Credentials:</strong><br>
            Student: student@diu.edu.bd / student123<br>
            Teacher: teacher@diu.edu.bd / teacher123
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}

function selectRole(role) {
    // Remove active class from all buttons
    document.querySelectorAll('.account-type-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to selected button
    document.querySelector(`[data-role="${role}"]`).classList.add('active');
    
    // Update hidden input
    document.getElementById('roleInput').value = role;
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
