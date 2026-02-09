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
    <title>Expense Tracker - <?php echo date('H:i:s'); ?></title>
    <link rel="icon" type="image/png" href="images/spending.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
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
                    <input type="number" id="expenseAmount" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="expenseMessage">Message:</label>
                    <input type="text" id="expenseMessage" placeholder="Optional message">
                </div>
                
                <div class="form-group">
                    <label>Category:</label>
                    <div class="category-buttons">
                        <button type="button" class="category-btn" data-category="Domestic">Domestic</button>
                        <button type="button" class="category-btn" data-category="Food">Food</button>
                        <button type="button" class="category-btn" data-category="Travel">Travel</button>
                        <button type="button" class="category-btn" data-category="Medical">Medical</button>
                        <button type="button" class="category-btn" data-category="Entertainment">Entertainment</button>
                        <button type="button" class="category-btn" data-category="Shopping">Shopping</button>
                        <button type="button" class="category-btn" data-category="Education">Education</button>
                        <button type="button" class="category-btn" data-category="Other">Other</button>
                    </div>
                </div>
                
                <input type="hidden" id="selectedCategory" value="Domestic">
                
                <button type="submit" class="btn btn-primary">Add Expense</button>
            </form>
        </section>

        <section class="expenses-section">
            <h2>Expenses</h2>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <label for="dateFilter">Filter by Date:</label>
                    <input type="date" id="dateFilter">
                </div>
                
                <div class="filter-group">
                    <label>Filter by Category:</label>
                    <div class="category-pills">
                        <button class="pill-btn" data-category="">All</button>
                        <button class="pill-btn" data-category="Domestic">Domestic</button>
                        <button class="pill-btn" data-category="Food">Food</button>
                        <button class="pill-btn" data-category="Travel">Travel</button>
                        <button class="pill-btn" data-category="Medical">Medical</button>
                        <button class="pill-btn" data-category="Entertainment">Entertainment</button>
                        <button class="pill-btn" data-category="Shopping">Shopping</button>
                        <button class="pill-btn" data-category="Education">Education</button>
                        <button class="pill-btn" data-category="Other">Other</button>
                    </div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="total-section">
                <h3>Total: ₹<span id="totalAmount">0.00</span></h3>
            </div>

            <!-- Expenses Table -->
            <div class="table-container">
                <table class="expenses-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expensesTableBody">
                        <!-- Expenses will be loaded here -->
                    </tbody>
                </table>
                
                <div id="noExpenses" class="no-expenses" style="display: none;">
                    No expenses found.
                </div>
            </div>
        </section>
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
    </script>
</body>
</html>
