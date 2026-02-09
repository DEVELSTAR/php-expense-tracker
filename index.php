<?php
session_start();
require_once 'api/db.php';

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
    <title>Akibworks Expense Tracker</title>
    <link rel="icon" type="image/png" href="images/spending.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Akibworks Expense Tracker</h1>
            <div class="auth-section">
                <?php if ($is_logged_in): ?>
                    <span class="auth-info">Logged in as <strong><?php echo htmlspecialchars($current_user['username']); ?></strong></span>
                    <button class="btn btn-secondary" onclick="window.location.href='logout.php'">Logout</button>
                <?php elseif ($is_guest): ?>
                    <span class="auth-info">Guest Mode (Public Expenses)</span>
                    <button class="btn btn-primary" onclick="showLoginModal()">Login</button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="showLoginModal()">Login</button>
                    <button class="btn btn-secondary" onclick="window.location.href='register.php'">Register</button>
                <?php endif; ?>
            </div>
        </header>

        <!-- Add Expense Form -->
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
                    <label>Category:</label>
                    <div class="button-group">
                        <button type="button" class="category-btn" data-category="Both">Both</button>
                        <button type="button" class="category-btn" data-category="Domestic" data-selected>Domestic</button>
                        <button type="button" class="category-btn" data-category="Akib">Akib</button>
                        <button type="button" class="category-btn" data-category="Saniya">Saniya</button>
                        <button type="button" class="category-btn" data-category="Neha">Neha</button>
                        <button type="button" class="category-btn" data-category="Family">Family</button>
                        <input type="hidden" id="selectedCategory" name="category" value="Domestic">
                    </div>
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
                <div class="pill-group">
                    <button type="button" class="pill-btn" data-category="">All</button>
                    <button type="button" class="pill-btn" data-category="Both">Both</button>
                    <button type="button" class="pill-btn" data-category="Domestic">Domestic</button>
                    <button type="button" class="pill-btn" data-category="Akib">Akib</button>
                    <button type="button" class="pill-btn" data-category="Saniya">Saniya</button>
                    <button type="button" class="pill-btn" data-category="Neha">Neha</button>
                    <button type="button" class="pill-btn" data-category="Family">Family</button>
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

    <!-- Login Modal -->
    <div id="loginModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Login</h3>
                <span class="close" onclick="hideLoginModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="form-group">
                        <label for="loginUsername">Username:</label>
                        <input type="text" id="loginUsername" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password:</label>
                        <input type="password" id="loginPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <p class="auth-note">
                        <small>Guest login: username "guest" with any password</small>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        function showLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
        }

        function hideLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('loginModal');
            if (event.target === modal) {
                hideLoginModal();
            }
        }
    </script>
</body>
</html>
