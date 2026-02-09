// Expense Tracker Application - Pure JavaScript Version
class ExpenseTracker {
    constructor() {
        this.expenses = [];
        this.currentFilter = '';
        this.currentUser = this.getCurrentUser();
        
        this.initializeElements();
        this.bindEvents();
        this.setDefaultDate();
        this.updateAuthUI();
        this.loadExpenses();
    }

    getCurrentUser() {
        const userStr = localStorage.getItem('currentUser');
        return userStr ? JSON.parse(userStr) : null;
    }

    setCurrentUser(user) {
        if (user) {
            localStorage.setItem('currentUser', JSON.stringify(user));
        } else {
            localStorage.removeItem('currentUser');
        }
        this.currentUser = user;
        this.updateAuthUI();
    }

    initializeElements() {
        // Form elements
        this.expenseForm = document.getElementById('expenseForm');
        this.expenseDateInput = document.getElementById('expenseDate');
        this.totalAmountInput = document.getElementById('expenseAmount');
        this.messageInput = document.getElementById('expenseMessage');
        this.categoryButtons = document.querySelectorAll('.category-btn');
        this.selectedCategoryInput = document.getElementById('selectedCategory');
        
        // Filter elements
        this.dateFilterInput = document.getElementById('dateFilter');
        this.pillButtons = document.querySelectorAll('.pill-btn');
        
        // Filter state
        this.filterCategory = '';
        this.filterDate = null;
        
        // Display elements
        this.totalAmountDisplay = document.getElementById('totalAmount');
        this.expensesTableBody = document.getElementById('expensesTableBody');
        this.noExpensesMessage = document.getElementById('noExpenses');
        this.loadingIndicator = document.getElementById('loading');
        this.errorMessage = document.getElementById('errorMessage');
        this.successMessage = document.getElementById('successMessage');
        
        // Auth elements
        this.authInfo = document.getElementById('authInfo');
        this.authButton = document.getElementById('authButton');
        
        // Modal forms
        this.loginForm = document.getElementById('loginForm');
        this.registerForm = document.getElementById('registerForm');
    }

    bindEvents() {
        // Form submission
        this.expenseForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.addExpense();
        });

        // Category button clicks (form section)
        this.categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.selectCategory(button);
            });
        });

        // Category pill button clicks (filter section)
        this.pillButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.selectFilterCategory(button);
            });
        });

        // Date filter change
        this.dateFilterInput.addEventListener('change', () => {
            this.filterDate = this.dateFilterInput.value;
            this.applyFilters();
        });

        // Login form
        this.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        // Register form
        this.registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegister();
        });

        // Hide messages when clicking on them
        this.errorMessage.addEventListener('click', () => this.hideMessage('error'));
        this.successMessage.addEventListener('click', () => this.hideMessage('success'));
    }

    updateAuthUI() {
        if (this.currentUser) {
            this.authInfo.textContent = `Logged in as ${this.currentUser.username}`;
            this.authButton.textContent = 'Logout';
            this.authButton.onclick = () => this.logout();
        } else {
            this.authInfo.textContent = 'Guest Mode (Public Expenses)';
            this.authButton.textContent = 'Login';
            this.authButton.onclick = () => showLoginModal();
        }
    }

    setDefaultDate() {
        const today = new Date().toISOString().split('T')[0];
        this.expenseDateInput.value = today;
        
        // Set default filter state
        this.filterCategory = '';
        this.filterDate = null;
        
        // Select "All" pill button by default (filter section)
        const allFilterButton = document.querySelector('.pill-btn[data-category=""]');
        if (allFilterButton) {
            allFilterButton.setAttribute('data-selected', '');
        }
        
        // Select "Domestic" category button by default (form section)
        const domesticButton = document.querySelector('.category-btn[data-category="Domestic"]');
        if (domesticButton) {
            domesticButton.setAttribute('data-selected', '');
        }
    }

    loadExpenses() {
        this.showLoading();
        this.hideMessage('error');
        this.hideMessage('success');

        // Load expenses from localStorage
        const allExpenses = this.getAllExpensesFromStorage();
        
        // Filter expenses based on current user and filters
        this.expenses = this.filterExpenses(allExpenses);
        
        // Calculate total
        const total = this.expenses.reduce((sum, expense) => sum + parseFloat(expense.total), 0);
        this.updateTotalDisplay(total);
        
        // Render expenses
        this.renderExpenses();
        
        this.hideLoading();
    }

    getAllExpensesFromStorage() {
        const expensesStr = localStorage.getItem('expenses');
        return expensesStr ? JSON.parse(expensesStr) : [];
    }

    saveExpensesToStorage(expenses) {
        localStorage.setItem('expenses', JSON.stringify(expenses));
    }

    filterExpenses(allExpenses) {
        let filtered = allExpenses;
        
        // Filter by user
        if (this.currentUser) {
            filtered = filtered.filter(expense => expense.user_id === this.currentUser.id);
        } else {
            filtered = filtered.filter(expense => expense.user_id === 'guest' || !expense.user_id);
        }
        
        // Filter by category
        if (this.filterCategory && this.filterCategory !== '') {
            filtered = filtered.filter(expense => expense.category === this.filterCategory);
        }
        
        // Filter by date
        if (this.filterDate && this.filterDate !== '') {
            filtered = filtered.filter(expense => expense.expense_date === this.filterDate);
        }
        
        // Sort by date (newest first)
        filtered.sort((a, b) => new Date(b.expense_date) - new Date(a.expense_date));
        
        return filtered;
    }

    addExpense() {
        const selectedCategory = this.selectedCategoryInput.value;
        
        if (!selectedCategory) {
            this.showError('Please select a category');
            return;
        }

        const expenseData = {
            id: Date.now(), // Simple ID using timestamp
            expense_date: this.expenseDateInput.value,
            total: parseFloat(this.totalAmountInput.value),
            category: selectedCategory,
            message: this.messageInput.value.trim(),
            user_id: this.currentUser ? this.currentUser.id : 'guest',
            created_at: new Date().toISOString()
        };

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            // Get existing expenses
            const allExpenses = this.getAllExpensesFromStorage();
            
            // Add new expense
            allExpenses.push(expenseData);
            
            // Save to localStorage
            this.saveExpensesToStorage(allExpenses);
            
            this.showSuccess('Expense added successfully');
            this.expenseForm.reset();
            this.setDefaultDate();
            this.loadExpenses();

        } catch (error) {
            this.showError(`Error adding expense: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    deleteExpense(id) {
        if (!confirm('Are you sure you want to delete this expense?')) {
            return;
        }

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            // Get existing expenses
            const allExpenses = this.getAllExpensesFromStorage();
            
            // Find and remove expense
            const updatedExpenses = allExpenses.filter(expense => expense.id !== id);
            
            // Save to localStorage
            this.saveExpensesToStorage(updatedExpenses);
            
            this.showSuccess('Expense deleted successfully');
            this.loadExpenses();

        } catch (error) {
            this.showError(`Error deleting expense: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    selectCategory(selectedButton) {
        // Remove selected from all category buttons (form section)
        this.categoryButtons.forEach(button => {
            button.removeAttribute('data-selected');
        });
        
        // Add selected to clicked button
        selectedButton.setAttribute('data-selected', '');
        
        // Update hidden input value
        this.selectedCategoryInput.value = selectedButton.getAttribute('data-category');
    }

    selectFilterCategory(selectedButton) {
        // Remove selected from all filter buttons
        this.pillButtons.forEach(button => {
            button.removeAttribute('data-selected');
        });
        
        // Add selected to clicked button
        selectedButton.setAttribute('data-selected', '');
        
        // Update selected category state for filtering
        this.filterCategory = selectedButton.getAttribute('data-category');
        
        // Apply filters immediately
        this.applyFilters();
    }

    applyFilters() {
        // Load filtered expenses immediately
        this.loadExpenses();
    }

    handleLogin() {
        const username = document.getElementById('loginUsername').value.trim();
        const password = document.getElementById('loginPassword').value;
        
        this.hideMessages();
        
        // Simple authentication logic
        if (username === 'guest') {
            // Guest login - always succeeds
            const guestUser = {
                id: 'guest',
                username: 'Guest'
            };
            this.setCurrentUser(guestUser);
            this.showSuccess('Logged in as Guest');
            hideLoginModal();
            this.loadExpenses();
        } else if (username && password) {
            // Check if user exists in localStorage (simple demo)
            const usersStr = localStorage.getItem('users');
            const users = usersStr ? JSON.parse(usersStr) : {};
            
            if (users[username] && users[username].password === password) {
                this.setCurrentUser(users[username]);
                this.showSuccess('Login successful');
                hideLoginModal();
                this.loadExpenses();
            } else {
                this.showError('Invalid username or password');
            }
        } else {
            this.showError('Please enter username and password');
        }
    }

    handleRegister() {
        const username = document.getElementById('registerUsername').value.trim();
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        this.hideMessages();
        
        if (password !== confirmPassword) {
            this.showError('Passwords do not match');
            return;
        }
        
        if (username.length < 3) {
            this.showError('Username must be at least 3 characters');
            return;
        }
        
        if (password.length < 6) {
            this.showError('Password must be at least 6 characters');
            return;
        }
        
        try {
            // Get existing users
            const usersStr = localStorage.getItem('users');
            const users = usersStr ? JSON.parse(usersStr) : {};
            
            // Check if username already exists
            if (users[username]) {
                this.showError('Username already exists');
                return;
            }
            
            // Create new user
            const newUser = {
                id: Date.now().toString(),
                username: username,
                password: password
            };
            
            users[username] = newUser;
            
            // Save users to localStorage
            localStorage.setItem('users', JSON.stringify(users));
            
            // Auto-login new user
            this.setCurrentUser(newUser);
            this.showSuccess('Registration successful');
            hideRegisterModal();
            this.loadExpenses();
            
        } catch (error) {
            this.showError('Registration failed: ' + error.message);
        }
    }

    logout() {
        this.setCurrentUser(null);
        this.showSuccess('Logged out successfully');
        this.loadExpenses();
    }

    renderExpenses() {
        this.expensesTableBody.innerHTML = '';

        if (this.expenses.length === 0) {
            this.expensesTableBody.style.display = 'none';
            this.noExpensesMessage.style.display = 'block';
            return;
        }

        this.expensesTableBody.style.display = '';
        this.noExpensesMessage.style.display = 'none';

        this.expenses.forEach((expense, index) => {
            const row = this.createExpenseRow(expense, index === 0);
            this.expensesTableBody.appendChild(row);
        });
    }

    createExpenseRow(expense, isNew = false) {
        const row = document.createElement('tr');
        if (isNew) {
            row.classList.add('new-row');
        }

        // Format date
        const formattedDate = new Date(expense.expense_date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        // Format amount
        const formattedAmount = parseFloat(expense.total).toFixed(2);

        row.innerHTML = `
            <td>${formattedDate}</td>
            <td>₹${formattedAmount}</td>
            <td><span class="category-badge category-${expense.category.toLowerCase()}">${expense.category}</span></td>
            <td>${expense.message || '-'}</td>
            <td>
                <button class="btn btn-danger" onclick="expenseTracker.deleteExpense(${expense.id})">
                    Delete
                </button>
            </td>
        `;

        return row;
    }

    updateTotalDisplay(total) {
        this.totalAmountDisplay.textContent = parseFloat(total).toFixed(2);
    }

    showLoading() {
        this.loadingIndicator.style.display = 'block';
    }

    hideLoading() {
        this.loadingIndicator.style.display = 'none';
    }

    showError(message) {
        this.errorMessage.textContent = message;
        this.errorMessage.style.display = 'block';
    }

    showSuccess(message) {
        this.successMessage.textContent = message;
        this.successMessage.style.display = 'block';
    }

    hideMessage(type) {
        if (type === 'error') {
            this.errorMessage.style.display = 'none';
        } else if (type === 'success') {
            this.successMessage.style.display = 'none';
        }
    }

    hideMessages() {
        this.hideMessage('error');
        this.hideMessage('success');
    }
}

// Initialize the application
const expenseTracker = new ExpenseTracker();
