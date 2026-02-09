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
    <title>Akibworks Expense Tracker</title>
    <link rel="icon" type="image/png" href="images/spending.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>Akibworks Expense Tracker</h1>
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
                    <a href="index.php" class="nav-link active">Expenses</a>
                </div>
                
                <!-- User Info -->
                <?php if ($is_logged_in || $is_guest): ?>
                    <div class="nav-user">
                        <span class="user-status">
                            <?php if ($is_logged_in): ?>
                                Logged in as <strong><?php echo htmlspecialchars($current_user['username']); ?></strong>
                            <?php else: ?>
                                Guest Mode
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Authentication Cards (shown when not logged in) -->
        <?php if (!$is_logged_in && !$is_guest): ?>
            <div class="auth-cards">
                <div class="auth-card">
                    <div class="auth-card-header">
                        <h3>🔐 Login</h3>
                        <p>Access your existing expenses</p>
                    </div>
                    <div class="auth-card-body">
                        <form id="loginCardForm">
                            <div class="form-group">
                                <label for="loginUsername">Username:</label>
                                <input type="text" id="loginUsername" required>
                            </div>
                            <div class="form-group">
                                <label for="loginPassword">Password:</label>
                                <input type="password" id="loginPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">Login</button>
                            <p class="auth-note">
                                <small>Guest login: username "guest" with any password</small>
                            </p>
                        </form>
                    </div>
                </div>

                <div class="auth-card">
                    <div class="auth-card-header">
                        <h3>📝 Sign Up</h3>
                        <p>Create a new account</p>
                    </div>
                    <div class="auth-card-body">
                        <form id="registerCardForm">
                            <div class="form-group">
                                <label for="registerUsername">Username:</label>
                                <input type="text" id="registerUsername" required minlength="3">
                                <small>At least 3 characters</small>
                            </div>
                            <div class="form-group">
                                <label for="registerPassword">Password:</label>
                                <input type="password" id="registerPassword" required minlength="6">
                                <small>At least 6 characters</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password:</label>
                                <input type="password" id="confirmPassword" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-secondary btn-full">Sign Up</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Loading and Messages for Auth Cards -->
            <div id="authLoading" class="loading" style="display: none;">
                Processing...
            </div>
            <div id="authErrorMessage" class="error-message" style="display: none;"></div>
            <div id="authSuccessMessage" class="success-message" style="display: none;"></div>
        <?php endif; ?>

        <!-- Add Expense Form (shown when logged in) -->
        <?php if ($is_logged_in || $is_guest): ?>
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
        <?php endif; ?>
    </div>

    <script src="assets/js/app.js"></script>
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

        // Auth Card Forms
        const loginCardForm = document.getElementById('loginCardForm');
        const registerCardForm = document.getElementById('registerCardForm');
        
        if (loginCardForm) {
            loginCardForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const username = document.getElementById('loginUsername').value.trim();
                const password = document.getElementById('loginPassword').value;
                
                document.getElementById('authLoading').style.display = 'block';
                document.getElementById('authErrorMessage').style.display = 'none';
                document.getElementById('authSuccessMessage').style.display = 'none';
                
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
                        document.getElementById('authSuccessMessage').textContent = data.message;
                        document.getElementById('authSuccessMessage').style.display = 'block';
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        document.getElementById('authErrorMessage').textContent = data.error;
                        document.getElementById('authErrorMessage').style.display = 'block';
                    }
                } catch (error) {
                    document.getElementById('authErrorMessage').textContent = 'Login failed: ' + error.message;
                    document.getElementById('authErrorMessage').style.display = 'block';
                } finally {
                    document.getElementById('authLoading').style.display = 'none';
                }
            });
        }
        
        if (registerCardForm) {
            registerCardForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const username = document.getElementById('registerUsername').value.trim();
                const password = document.getElementById('registerPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (password !== confirmPassword) {
                    document.getElementById('authErrorMessage').textContent = 'Passwords do not match';
                    document.getElementById('authErrorMessage').style.display = 'block';
                    return;
                }
                
                document.getElementById('authLoading').style.display = 'block';
                document.getElementById('authErrorMessage').style.display = 'none';
                document.getElementById('authSuccessMessage').style.display = 'none';
                
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
                        document.getElementById('authSuccessMessage').textContent = data.message;
                        document.getElementById('authSuccessMessage').style.display = 'block';
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        document.getElementById('authErrorMessage').textContent = data.error;
                        document.getElementById('authErrorMessage').style.display = 'block';
                    }
                } catch (error) {
                    document.getElementById('authErrorMessage').textContent = 'Registration failed: ' + error.message;
                    document.getElementById('authErrorMessage').style.display = 'block';
                } finally {
                    document.getElementById('authLoading').style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
