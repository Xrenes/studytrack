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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (in_array($role, ['student', 'teacher']) && !str_ends_with($email, '@diu.edu.bd')) {
        $error = 'Student and Teacher accounts must use @diu.edu.bd email';
    } elseif (in_array($role, ['student', 'teacher']) && empty($studentId)) {
        $error = 'Student/Employee ID is required for institutional accounts';
    } else {
        // Check if email already exists in database
        $existingUser = getUserByEmail($email);
        
        if ($existingUser) {
            $error = 'Email already registered';
        } else {
            // Hash password and create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                createUser([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'student_id' => $role !== 'personal' ? $studentId : null,
                    'role' => $role
                ]);
                
                $success = 'Registration successful! You can now login with your credentials.';
            } catch (Exception $e) {
                $error = 'Registration failed. Please try again.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Register';
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
        margin: var(--spacing-xl) 0;
    }
    
    .logo-container {
        text-align: center;
        margin-bottom: var(--spacing-xl);
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
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
    }
    
    .account-type-btn {
        padding: 10px;
        background: var(--bg-tertiary);
        border: 2px solid transparent;
        border-radius: var(--radius-sm);
        color: var(--text-primary);
        font-size: var(--font-size-sm);
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
    
    .account-type-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        position: relative;
    }
    
    .account-type-btn.disabled:hover {
        border-color: transparent;
    }
    
    .coming-soon-badge {
        display: block;
        font-size: 9px;
        font-weight: 700;
        color: var(--text-tertiary);
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
    
    .password-requirements {
        font-size: var(--font-size-xs);
        color: var(--text-tertiary);
        margin-top: 4px;
    }
    
    #studentIdGroup {
        display: block;
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
            <p class="text-secondary text-small">Create your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <div class="mt-sm">
                    <a href="/auth/login.php" style="color: var(--success); text-decoration: underline;">Go to Login</a>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter your full name" 
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label>Account Type *</label>
                <div class="account-type-selector">
                    <button type="button" class="account-type-btn active" data-role="student" onclick="selectRole('student')">
                        Student
                    </button>
                    <button type="button" class="account-type-btn" data-role="teacher" onclick="selectRole('teacher')">
                        Teacher
                    </button>
                    <button type="button" class="account-type-btn disabled" data-role="personal" disabled>
                        Personal
                        <span class="coming-soon-badge">Coming Soon</span>
                    </button>
                </div>
                <input type="hidden" name="role" id="roleInput" value="student">
            </div>
            
            <div class="form-group" id="studentIdGroup">
                <label for="student_id">Student/Employee ID *</label>
                <input 
                    type="text" 
                    id="student_id" 
                    name="student_id" 
                    placeholder="e.g., 241-15-227" 
                    value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your.name@diu.edu.bd" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
                <div class="password-requirements" id="emailHint">
                    Student and Teacher accounts must use @diu.edu.bd email
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Create a password"
                        required
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword('password')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon1">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <div class="password-requirements">
                    Minimum 8 characters, include uppercase, lowercase, and numbers
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <div class="password-toggle">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        required
                    >
                    <button type="button" class="toggle-btn" onclick="togglePassword('confirm_password')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>
        
        <div class="helper-text">
            Already have an account? <a href="/auth/login.php">Sign in</a>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const eyeIconId = fieldId === 'password' ? 'eyeIcon1' : 'eyeIcon2';
    const eyeIcon = document.getElementById(eyeIconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}

function selectRole(role) {
    // Prevent selection of personal role
    if (role === 'personal') {
        return;
    }
    
    // Remove active class from all buttons
    document.querySelectorAll('.account-type-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to selected button
    document.querySelector(`[data-role="${role}"]`).classList.add('active');
    
    // Update hidden input
    document.getElementById('roleInput').value = role;
    
    // Show/hide student ID field and email hint
    const studentIdGroup = document.getElementById('studentIdGroup');
    const studentIdInput = document.getElementById('student_id');
    const emailHint = document.getElementById('emailHint');
    
    if (role === 'personal') {
        studentIdGroup.style.display = 'none';
        studentIdInput.required = false;
        emailHint.style.display = 'none';
    } else {
        studentIdGroup.style.display = 'block';
        studentIdInput.required = true;
        emailHint.style.display = 'block';
    }
}

// Initialize on page load
selectRole('student');
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
