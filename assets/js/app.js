// Expense Tracker Application - PHP Session Version
class ExpenseTracker {
    constructor() {
        this.apiUrl = 'api';
        this.expenses = [];
        this.categories = [];
        this.currentFilter = '';
        this.currentUser = null;
        
        this.initializeElements();
        this.bindEvents();
        this.setDefaultDate();
        this.loadCategories();
        this.loadExpenses();
    }

    initializeElements() {
        // Form elements
        this.expenseForm = document.getElementById('expenseForm');
        this.expenseDateInput = document.getElementById('expenseDate');
        this.totalAmountInput = document.getElementById('expenseAmount');
        this.messageInput = document.getElementById('expenseMessage');
        this.categoryButtons = document.getElementById('categoryButtons');
        this.selectedCategoryInput = document.getElementById('selectedCategory');
        
        // Filter elements
        this.dateFilterInput = document.getElementById('dateFilter');
        this.categoryFilterPills = document.getElementById('categoryFilterPills');
        
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
        
        // Select "All" pill button by default (filter section)
        // This will be handled by populateCategoryFilterPills() which now selects "All" by default
    }

    async loadCategories() {
        try {
            const response = await fetch('/api/get_categories.php');
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to load categories');
            }

            if (data.success) {
                this.categories = data.categories;
                this.populateCategoryButtons();
                this.populateCategoryFilterPills();
                this.renderCategoryList();
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error loading categories: ${error.message}`);
        }
    }

    populateCategoryButtons() {
        this.categoryButtons.innerHTML = '';
        
        this.categories.forEach((category, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'category-btn';
            button.setAttribute('data-category', category.name);
            button.textContent = category.name;
            
            // Select first category by default
            if (index === 0) {
                button.setAttribute('data-selected', '');
                this.selectedCategoryInput.value = category.name;
            }
            
            button.addEventListener('click', () => {
                this.selectCategory(button);
            });
            
            this.categoryButtons.appendChild(button);
        });
    }

    populateCategoryFilterPills() {
        this.categoryFilterPills.innerHTML = '';
        
        // Create "All" button
        const allPill = document.createElement('button');
        allPill.type = 'button';
        allPill.className = 'pill-btn';
        allPill.setAttribute('data-category', '');
        allPill.textContent = 'All';
        
        // Select "All" by default
        allPill.setAttribute('data-selected', '');
        
        allPill.addEventListener('click', () => {
            this.selectFilterCategory(allPill);
        });
        
        this.categoryFilterPills.appendChild(allPill);
        
        // Create category pills
        this.categories.forEach(category => {
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.className = 'pill-btn';
            pill.setAttribute('data-category', category.name);
            pill.textContent = category.name;
            
            pill.addEventListener('click', () => {
                this.selectFilterCategory(pill);
            });
            
            this.categoryFilterPills.appendChild(pill);
        });
    }

    async addCategory(name) {
        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const response = await fetch('/api/create_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: name })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to add category');
            }

            if (data.success) {
                this.showSuccess('Category added successfully');
                document.getElementById('newCategoryName').value = '';
                this.loadCategories(); // Reload categories to update UI
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error adding category: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    async deleteCategory(categoryId) {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }

        try {
            this.showLoading();
            this.hideMessage('error');
            this.hideMessage('success');

            const response = await fetch('/api/delete_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: categoryId })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Failed to delete category');
            }

            if (data.success) {
                this.showSuccess('Category deleted successfully');
                this.loadCategories(); // Reload categories to update UI
            } else {
                throw new Error(data.error || 'Unknown error occurred');
            }

        } catch (error) {
            this.showError(`Error deleting category: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    renderCategoryList() {
        const categoryList = document.getElementById('categoryList');
        if (!categoryList) return;

        categoryList.innerHTML = '';

        this.categories.forEach(category => {
            const categoryItem = document.createElement('div');
            categoryItem.className = 'category-item';
            
            const isDefault = category.user_id === null;
            
            categoryItem.innerHTML = `
                <div class="category-info">
                    <span class="category-name">${category.name}</span>
                    ${isDefault ? '<span class="category-badge">Default</span>' : '<span class="category-badge">Custom</span>'}
                </div>
                ${!isDefault ? `<button class="btn btn-danger btn-sm" onclick="expenseTracker.deleteCategory(${category.id})">Delete</button>` : ''}
            `;
            
            categoryList.appendChild(categoryItem);
        });
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

            const url = params.toString() ? `/api/get_expenses.php?${params.toString()}` : `/api/get_expenses.php`;

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

            const response = await fetch(`/api/add_expense.php`, {
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
                // Reset category selection to first category
                const firstCategoryBtn = this.categoryButtons.querySelector('.category-btn');
                if (firstCategoryBtn) {
                    this.selectCategory(firstCategoryBtn);
                }
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

            const response = await fetch(`/api/delete_expense.php?id=${id}`, {
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
        const allButtons = this.categoryButtons.querySelectorAll('.category-btn');
        allButtons.forEach(button => {
            button.removeAttribute('data-selected');
        });
        
        // Add selected to clicked button
        selectedButton.setAttribute('data-selected', '');
        
        // Update hidden input value
        this.selectedCategoryInput.value = selectedButton.getAttribute('data-category');
    }

    selectFilterCategory(selectedButton) {
        // Remove selected from all filter buttons
        const allPills = this.categoryFilterPills.querySelectorAll('.pill-btn');
        allPills.forEach(button => {
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
