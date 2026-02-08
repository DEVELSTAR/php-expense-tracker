// Expense Tracker Application
class ExpenseTracker {
    constructor() {
        this.apiUrl = 'api';
        this.expenses = [];
        this.currentFilter = '';
        
        this.initializeElements();
        this.bindEvents();
        this.setDefaultDate();
        this.loadExpenses();
    }

    initializeElements() {
        // Form elements
        this.expenseForm = document.getElementById('expenseForm');
        this.expenseDateInput = document.getElementById('expenseDate');
        this.totalAmountInput = document.getElementById('totalAmount');
        this.categoryRadios = document.querySelectorAll('input[name="category"]');
        
        // Filter elements
        this.categoryFilterRadios = document.querySelectorAll('input[name="categoryFilter"]');
        
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

        // Category filter change
        this.categoryFilterRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const selectedRadio = document.querySelector('input[name="categoryFilter"]:checked');
                this.currentFilter = selectedRadio.value;
                this.loadExpenses();
            });
        });

        // Hide messages when clicking on them
        this.errorMessage.addEventListener('click', () => this.hideMessage('error'));
        this.successMessage.addEventListener('click', () => this.hideMessage('success'));
    }

    setDefaultDate() {
        const today = new Date().toISOString().split('T')[0];
        this.expenseDateInput.value = today;
    }

    async loadExpenses() {
        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const url = this.currentFilter 
                ? `${this.apiUrl}/get_expenses.php?category=${encodeURIComponent(this.currentFilter)}`
                : `${this.apiUrl}/get_expenses.php`;

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
        const selectedCategory = document.querySelector('input[name="category"]:checked');
        
        if (!selectedCategory) {
            this.showError('Please select a category');
            return;
        }

        const expenseData = {
            expense_date: this.expenseDateInput.value,
            total: parseFloat(this.totalAmountInput.value),
            category: selectedCategory.value
        };

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const response = await fetch(`${this.apiUrl}/add_expense.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(expenseData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to add expense');
            }

            if (data.success) {
                this.showSuccess('Expense added successfully!');
                this.expenseForm.reset();
                this.setDefaultDate();
                await this.loadExpenses();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error adding expense: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    async deleteExpense(expenseId) {
        if (!confirm('Are you sure you want to delete this expense?')) {
            return;
        }

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const response = await fetch(`${this.apiUrl}/delete_expense.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: expenseId })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to delete expense');
            }

            if (data.success) {
                this.showSuccess('Expense deleted successfully!');
                await this.loadExpenses();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error deleting expense: ${error.message}`);
        } finally {
            this.hideLoading();
        }
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
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.hideMessage('error');
        }, 5000);
    }

    showSuccess(message) {
        this.successMessage.textContent = message;
        this.successMessage.style.display = 'block';
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            this.hideMessage('success');
        }, 3000);
    }

    hideMessage(type) {
        if (type === 'error') {
            this.errorMessage.style.display = 'none';
        } else if (type === 'success') {
            this.successMessage.style.display = 'none';
        }
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.expenseTracker = new ExpenseTracker();
});

// Add some CSS for category badges
const categoryStyles = `
    .category-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .category-both { background: #e3f2fd; color: #1976d2; }
    .category-domestic { background: #f3e5f5; color: #7b1fa2; }
    .category-akib { background: #e8f5e8; color: #388e3c; }
    .category-saniya { background: #fff3e0; color: #f57c00; }
    .category-neha { background: #fce4ec; color: #c2185b; }
    .category-family { background: #e0f2f1; color: #00695c; }
`;

// Inject category styles
const styleSheet = document.createElement('style');
styleSheet.textContent = categoryStyles;
document.head.appendChild(styleSheet);
