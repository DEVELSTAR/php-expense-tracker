// Authentication Module
class AuthManager {
    constructor() {
        this.initializeElements();
        this.bindEvents();
        this.checkAuthStatus();
    }

    initializeElements() {
        this.loginForm = document.getElementById('loginForm');
        this.registerForm = document.getElementById('registerForm');
        this.loginUsername = document.getElementById('loginUsername');
        this.loginPassword = document.getElementById('loginPassword');
        this.registerUsername = document.getElementById('username');
        this.registerPassword = document.getElementById('password');
        this.confirmPassword = document.getElementById('confirmPassword');
        this.loading = document.getElementById('loading');
        this.errorMessage = document.getElementById('errorMessage');
        this.successMessage = document.getElementById('successMessage');
    }

    bindEvents() {
        if (this.loginForm) {
            this.loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        }

        if (this.registerForm) {
            this.registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister();
            });
        }
    }

    async handleLogin() {
        const username = this.loginUsername.value.trim();
        const password = this.loginPassword.value;
        
        this.showLoading();
        this.hideMessages();
        
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError('Login failed: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async handleRegister() {
        const username = this.registerUsername.value.trim();
        const password = this.registerPassword.value;
        const confirmPassword = this.confirmPassword.value;
        
        this.hideMessages();
        
        // Validate passwords match
        if (password !== confirmPassword) {
            this.showError('Passwords do not match');
            return;
        }
        
        this.showLoading();
        
        try {
            const response = await fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(data.message);
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError('Registration failed: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    showLoading() {
        if (this.loading) this.loading.style.display = 'block';
    }

    hideLoading() {
        if (this.loading) this.loading.style.display = 'none';
    }

    showError(message) {
        if (this.errorMessage) {
            this.errorMessage.textContent = message;
            this.errorMessage.style.display = 'block';
        }
    }

    showSuccess(message) {
        if (this.successMessage) {
            this.successMessage.textContent = message;
            this.successMessage.style.display = 'block';
        }
    }

    hideMessages() {
        this.hideError();
        this.hideSuccess();
    }

    hideError() {
        if (this.errorMessage) this.errorMessage.style.display = 'none';
    }

    hideSuccess() {
        if (this.successMessage) this.successMessage.style.display = 'none';
    }

    checkAuthStatus() {
        // Check if user is logged in and redirect if needed
        const currentPath = window.location.pathname;
        if (currentPath.includes('index.php') || currentPath.endsWith('/')) {
            // User is on main page, no redirect needed
            return;
        }
        
        // If not logged in and not on login/register pages, redirect to login
        fetch('api/check_auth.php')
            .then(response => response.json())
            .then(data => {
                if (!data.logged_in && !currentPath.includes('login.php') && !currentPath.includes('register.php')) {
                    window.location.href = 'index.php';
                }
            })
            .catch(error => console.error('Auth check failed:', error));
    }
}

// Initialize auth manager if on login/register pages
if (document.getElementById('loginForm') || document.getElementById('registerForm')) {
    new AuthManager();
}
