// Expense Tracker Application - PHP Session Version
class ExpenseTracker {
    constructor() {
        this.apiUrl = 'api';
        this.expenses = [];
        this.currentFilter = '';
        this.currentUser = null;
        
        this.initializeElements();
        this.bindEvents();
        this.setDefaultDate();
        this.loadExpenses();
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

        // Hide messages when clicking on them
        this.errorMessage.addEventListener('click', () => this.hideMessage('error'));
        this.successMessage.addEventListener('click', () => this.hideMessage('success'));
    }

    setDefaultDate() {
        const today = new Date().toISOString().split('T')[0];
        this.expenseDateInput.value = today;
        
        // Set default filter state
        this.filterCategory = '';
        this.filterDate = null;
        
        // Reset all category buttons (form section)
        this.categoryButtons.forEach(button => {
            button.removeAttribute('data-selected');
        });
        
        // Select "Domestic" category button by default (form section)
        const domesticButton = document.querySelector('.category-btn[data-category="Domestic"]');
        if (domesticButton) {
            domesticButton.setAttribute('data-selected', '');
            this.selectedCategoryInput.value = 'Domestic';
        }
        
        // Select "All" pill button by default (filter section)
        const allFilterButton = document.querySelector('.pill-btn[data-category=""]');
        if (allFilterButton) {
            allFilterButton.setAttribute('data-selected', '');
        }
    }

    async loadExpenses() {
        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            // Build URL with filters
            const params = new URLSearchParams();
            
            if (this.filterCategory && this.filterCategory !== '') {
                params.append('category', this.filterCategory);
            }
            
            if (this.filterDate && this.filterDate !== '') {
                params.append('date', this.filterDate);
            }

            const url = params.toString() ? `${this.apiUrl}/get_expenses.php?${params.toString()}` : `${this.apiUrl}/get_expenses.php`;

            const response = await fetch(url);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to load expenses');
            }

            if (data.success) {
                this.expenses = data.expenses;
                this.updateTotalDisplay(data.total_sum);
                this.renderExpenses();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error loading expenses: ${error.message}`);
            this.expenses = [];
            this.updateTotalDisplay(0);
            this.renderExpenses();
        } finally {
            this.hideLoading();
        }
    }

    async addExpense() {
        const selectedCategory = this.selectedCategoryInput.value;
        
        if (!selectedCategory) {
            this.showError('Please select a category');
            return;
        }

        const expenseData = {
            expense_date: this.expenseDateInput.value,
            total: parseFloat(this.totalAmountInput.value),
            category: selectedCategory,
            message: this.messageInput.value.trim()
        };

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const response = await fetch(`${this.apiUrl}/add_expense.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(expenseData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to add expense');
            }

            if (data.success) {
                this.showSuccess('Expense added successfully');
                this.expenseForm.reset();
                this.setDefaultDate();
                this.loadExpenses();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error adding expense: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    async deleteExpense(id) {
        if (!confirm('Are you sure you want to delete this expense?')) {
            return;
        }

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const response = await fetch(`${this.apiUrl}/delete_expense.php?id=${id}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to delete expense');
            }

            if (data.success) {
                this.showSuccess('Expense deleted successfully');
                this.loadExpenses();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

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
