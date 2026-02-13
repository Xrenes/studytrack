/* ============================================
   StudyTrack - Authentication JavaScript
   Login, Registration & Dummy Data
   ============================================ */

// === DUMMY USER DATA ===
const dummyUsers = [
    {
        email: 'student@diu.edu.bd',
        password: 'student123',
        role: 'student',
        full_name: 'John Doe',
        student_id: '241-15-101',
        profile_photo: null
    },
    {
        email: 'jane.smith@diu.edu.bd',
        password: 'student123',
        role: 'student',
        full_name: 'Jane Smith',
        student_id: '241-15-102',
        profile_photo: null
    },
    {
        email: 'teacher@diu.edu.bd',
        password: 'teacher123',
        role: 'teacher',
        full_name: 'Prof. Sarah Wilson',
        student_id: 'T-001',
        profile_photo: null
    },
    {
        email: 'personal@gmail.com',
        password: 'personal123',
        role: 'personal',
        full_name: 'Alex Johnson',
        student_id: null,
        profile_photo: null
    }
];

// === UTILITY FUNCTIONS ===

// Show error message
function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        setTimeout(() => errorDiv.classList.remove('show'), 5000);
    }
}

// Show success message
function showSuccess(message) {
    const successDiv = document.getElementById('successMessage');
    if (successDiv) {
        successDiv.textContent = message;
        successDiv.classList.add('show');
        setTimeout(() => successDiv.classList.remove('show'), 5000);
    }
}

// Validate email format
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate institutional email
function isInstitutionalEmail(email) {
    return email.endsWith('@diu.edu.bd');
}

// Validate password strength
function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    
    return {
        isValid: password.length >= minLength && hasUpperCase && hasLowerCase && hasNumber,
        minLength: password.length >= minLength,
        hasUpperCase,
        hasLowerCase,
        hasNumber
    };
}

// === LOGIN FUNCTIONALITY ===

if (document.getElementById('loginForm')) {
    const loginForm = document.getElementById('loginForm');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const accountTypeBtns = document.querySelectorAll('.account-type-btn');

    // Password toggle
    togglePasswordBtn.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle icon
        const eyeIcon = document.getElementById('eyeIcon');
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            `;
        } else {
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        }
    });

    // Account type selection
    accountTypeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            accountTypeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Login form submission
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const role = document.querySelector('input[name="role"]:checked').value;

        // Validation
        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }

        // Show loading state
        const btnText = document.getElementById('loginBtnText');
        const spinner = document.getElementById('loginSpinner');
        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';

        // Simulate API call delay
        setTimeout(() => {
            // Find matching user
            const user = dummyUsers.find(u => 
                u.email === email && 
                u.password === password && 
                u.role === role
            );

            if (user) {
                // Store user data in localStorage
                localStorage.setItem('isLoggedIn', 'true');
                localStorage.setItem('currentUser', JSON.stringify(user));
                
                // Redirect to calendar
                window.location.href = 'calendar.html';
            } else {
                showError('Invalid email, password, or account type');
                btnText.style.display = 'inline';
                spinner.style.display = 'none';
            }
        }, 800);
    });
}

// === REGISTRATION FUNCTIONALITY ===

if (document.getElementById('registerForm')) {
    const registerForm = document.getElementById('registerForm');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const accountTypeBtns = document.querySelectorAll('.account-type-btn');
    const studentIdGroup = document.getElementById('studentIdGroup');

    // Password toggle
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    }

    if (toggleConfirmPasswordBtn) {
        toggleConfirmPasswordBtn.addEventListener('click', () => {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
        });
    }

    // Account type selection
    accountTypeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            accountTypeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const role = btn.querySelector('input[type="radio"]').value;
            
            // Show/hide student ID field
            if (role === 'personal') {
                studentIdGroup.style.display = 'none';
                document.getElementById('studentId').removeAttribute('required');
            } else {
                studentIdGroup.style.display = 'block';
                document.getElementById('studentId').setAttribute('required', 'required');
            }
        });
    });

    // Password strength indicator
    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            const validation = validatePassword(password);
            
            // Update requirements display (if exists)
            const requirements = document.querySelectorAll('.password-requirements li');
            if (requirements.length > 0) {
                requirements[0].classList.toggle('valid', validation.minLength);
                requirements[1].classList.toggle('valid', validation.hasUpperCase);
                requirements[2].classList.toggle('valid', validation.hasLowerCase);
                requirements[3].classList.toggle('valid', validation.hasNumber);
            }
        });
    }

    // Registration form submission
    registerForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const fullName = document.getElementById('fullName').value.trim();
        const studentId = document.getElementById('studentId').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const role = document.querySelector('input[name="role"]:checked').value;

        // Validation
        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }

        if (role !== 'personal' && !isInstitutionalEmail(email)) {
            showError('Students and Teachers must use @diu.edu.bd email');
            return;
        }

        const passwordValidation = validatePassword(password);
        if (!passwordValidation.isValid) {
            showError('Password does not meet requirements');
            return;
        }

        if (password !== confirmPassword) {
            showError('Passwords do not match');
            return;
        }

        // Check if email already exists
        const existingUser = dummyUsers.find(u => u.email === email);
        if (existingUser) {
            showError('An account with this email already exists');
            return;
        }

        // Show loading state
        const btnText = document.getElementById('registerBtnText');
        const spinner = document.getElementById('registerSpinner');
        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';

        // Simulate API call delay
        setTimeout(() => {
            // Create new user
            const newUser = {
                email: email,
                password: password,
                role: role,
                full_name: fullName,
                student_id: role === 'personal' ? null : studentId,
                profile_photo: null
            };

            // Add to dummy users array
            dummyUsers.push(newUser);

            // Store in localStorage
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('currentUser', JSON.stringify(newUser));

            // Show success and redirect
            showSuccess('Account created successfully!');
            setTimeout(() => {
                window.location.href = 'calendar.html';
            }, 1000);
        }, 800);
    });
}

// === LOGOUT FUNCTIONALITY ===

function logout() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('currentUser');
    window.location.href = 'login.html';
}

// === AUTH CHECK (for protected pages) ===

function checkAuth() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    const currentPage = window.location.pathname;

    // Redirect to login if not authenticated and not on login/register page
    if (!isLoggedIn && !currentPage.includes('login') && !currentPage.includes('register')) {
        window.location.href = 'login.html';
    }

    // Redirect to calendar if logged in and on login/register page
    if (isLoggedIn && (currentPage.includes('login') || currentPage.includes('register'))) {
        window.location.href = 'calendar.html';
    }
}

// === GET CURRENT USER ===

function getCurrentUser() {
    const userJson = localStorage.getItem('currentUser');
    return userJson ? JSON.parse(userJson) : null;
}

// === INITIALIZE AUTH ===

document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
});

// === EXPORT FOR USE IN OTHER SCRIPTS ===

window.StudyTrackAuth = {
    logout,
    getCurrentUser,
    checkAuth,
    showError,
    showSuccess
};
