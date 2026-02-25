<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !$_SESSION['is_guest'];
$is_guest = isset($_SESSION['user_id']) && $_SESSION['is_guest'];

// Get current user info
$current_user = null;
if ($is_logged_in) {
    $current_user = [
        'username' => $_SESSION['username'],
        'id' => $_SESSION['user_id']
    ];
} elseif ($is_guest) {
    $current_user = [
        'username' => 'Guest',
        'id' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Track Your Spending Efficiently | Akibworks</title>
    <meta name="description" content="A simple, intuitive expense tracking application with guest mode and user authentication. Manage personal and family expenses with categories and filtering.">
    <meta name="keywords" content="expense tracker, spending tracker, budget management, expense manager, financial tracking">
    <meta name="author" content="Akibworks">
    <meta name="theme-color" content="#2563eb">
    <link rel="canonical" href="https://expense.akibworks.in/">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <meta property="og:title" content="Expense Tracker - Manage Your Spending">
    <meta property="og:description" content="Track expenses with ease using our free expense tracker app with guest and user modes.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expense.akibworks.in/">
    <meta property="og:image" content="https://expense.akibworks.in/images/favicon.png">
    <meta property="og:site_name" content="Akibworks Expense Tracker">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Expense Tracker - Manage Your Spending">
    <meta name="twitter:description" content="Track expenses with ease using our free expense tracker app.">
    <meta name="twitter:image" content="https://expense.akibworks.in/images/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "Akibworks Expense Tracker",
      "description": "A simple, intuitive expense tracking application with guest mode and user authentication.",
      "url": "https://expense.akibworks.in",
      "applicationCategory": "FinanceApplication",
      "operatingSystem": "Web",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Akibworks",
        "logo": {
          "@type": "ImageObject",
          "url": "https://expense.akibworks.in/images/favicon.png",
          "width": "192",
          "height": "192"
        },
        "url": "https://expense.akibworks.in"
      }
    }
    </script>
</head>
<body>
    <!-- Debug: PHP Time: <?php echo date('Y-m-d H:i:s'); ?> -->
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>Expense Tracker</h1>
            </div>
            
            <!-- Hamburger Menu Button -->
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="nav-menu" id="navMenu">
                <div class="nav-links">
                    <?php if ($is_logged_in): ?>
                        <button class="nav-link nav-btn" onclick="window.location.href='logout.php'">Logout</button>
                        <div class="nav-user-inline">
                            <span class="user-status">
                                Logged in as <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>
                            </span>
                        </div>
                    <?php elseif ($is_guest): ?>
                        <button class="nav-link nav-btn" onclick="window.location.href='login.php'">Login</button>
                        <div class="nav-user-inline">
                            <span class="user-status">Guest Mode</span>
                        </div>
                    <?php else: ?>
                        <button class="nav-link nav-btn" onclick="window.location.href='login.php'">Login</button>
                        <button class="nav-link nav-btn" onclick="window.location.href='register.php'">Sign Up</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <section class="form-section">
            <h2>Add Expense</h2>
            <form id="expenseForm">
                <div class="form-group">
                    <label for="expenseDate">Date:</label>
                    <input type="date" id="expenseDate" required>
                </div>
                
                <div class="form-group">
                    <label for="expenseAmount">Total Amount:</label>
                    <input type="number" id="expenseAmount" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="expenseMessage">Message:</label>
                    <input type="text" id="expenseMessage" placeholder="Optional message for this expense">
                </div>
                
                <div class="form-group">
                    <label for="expenseCategory">Category:</label>
                    <div class="button-group" id="categoryButtons">
                        <!-- Category buttons will be populated here -->
                    </div>
                    <?php if ($is_logged_in): ?>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="showAddCategoryModal()">+ Add Category</button>
                    <?php endif; ?>
                    <input type="hidden" id="selectedCategory" name="category" value="">
                </div>
                
                <button type="submit" class="btn btn-primary">Add Expense</button>
            </form>
        </section>

        <!-- Filter and Total Section -->
        <section class="filter-section">
            <div class="filter-group">
                <label>Filter by Date:</label>
                <input type="date" id="dateFilter" class="date-filter" placeholder="All Dates">
            </div>
            
            <div class="filter-group">
                <label>Filter by Category:</label>
                <div class="pill-group" id="categoryFilterPills">
                    <button type="button" class="pill-btn" data-category="">All</button>
                </div>
            </div>
            
            <div class="total-display">
                <strong>Total Amount: ₹<span id="totalAmount">0.00</span></strong>
            </div>
        </section>

        <!-- Loading Indicator -->
        <div id="loading" class="loading" style="display: none;">
            Loading expenses...
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="error-message" style="display: none;"></div>

        <!-- Success Message -->
        <div id="successMessage" class="success-message" style="display: none;"></div>

        <!-- Expenses Table -->
        <section class="table-section">
            <h2>Expenses</h2>
            <div class="table-container">
                <table id="expensesTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Category</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expensesTableBody">
                        <!-- Expenses will be populated here -->
                    </tbody>
                </table>
                
                <div id="noExpenses" class="no-expenses" style="display: none;">
                    No expenses found.
                </div>
            </div>
        </section>
    </div>

    <!-- Category Management Modal -->
    <div id="categoryModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Manage Categories</h3>
                <button class="modal-close" onclick="hideCategoryModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="category-list" id="categoryList">
                    <!-- Categories will be populated here -->
                </div>
                <div class="add-category-form">
                    <h4>Add New Category</h4>
                    <div class="form-group">
                        <input type="text" id="newCategoryName" placeholder="Enter category name" maxlength="50">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addCategory()">Add Category</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        // Hamburger Menu Toggle
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });

        // Category Modal Functions
        function showAddCategoryModal() {
            document.getElementById('categoryModal').style.display = 'block';
            expenseTracker.loadCategories();
        }

        function hideCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function addCategory() {
            const nameInput = document.getElementById('newCategoryName');
            const name = nameInput.value.trim();
            
            if (!name) {
                expenseTracker.showError('Please enter a category name');
                return;
            }
            
            expenseTracker.addCategory(name);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target === modal) {
                hideCategoryModal();
            }
        });
    </script>
</body>
</html>
