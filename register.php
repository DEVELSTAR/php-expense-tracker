<?php
session_start();

// Handle registration POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validation
    if (strlen($username) < 3) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Username must be at least 3 characters']);
        exit;
    }
    
    if (strlen($password) < 6) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        exit;
    }
    
    // Load existing users
    $usersFile = __DIR__ . '/users.json';
    $users = [];
    
    if (file_exists($usersFile)) {
        $json = file_get_contents($usersFile);
        $users = json_decode($json, true) ?: [];
    }
    
    // Check if username already exists
    if (isset($users[$username])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Username already exists']);
        exit;
    }
    
    // Create new user
    $newUser = [
        'id' => time() + rand(1000, 9999),
        'username' => $username,
        'password' => $password, // In production, use password_hash()
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $users[$username] = $newUser;
    
    // Save users to JSON file (for backward compatibility)
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    
    // Also save user to database for category management
    try {
        require_once 'api/db.php';
        $dbStmt = $conn->prepare("INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)");
        $dbStmt->execute([$newUser['id'], $username, $password]);
    } catch(PDOException $e) {
        // If database insert fails, continue with JSON-based registration
        error_log("Database user insert failed: " . $e->getMessage());
    }
    
    // Auto-login new user
    $_SESSION['user_id'] = $newUser['id'];
    $_SESSION['username'] = $newUser['username'];
    $_SESSION['is_guest'] = false;
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user' => $newUser
    ]);
    exit;
}

// Show registration page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Account | Akibworks Expense Tracker</title>
    <meta name="description" content="Create a free account on Akibworks Expense Tracker and start managing your expenses securely.">
    <meta name="keywords" content="expense tracker register, create account, sign up, expense management">
    <meta name="author" content="Akibworks">
    <meta name="theme-color" content="#2563eb">
    <link rel="canonical" href="https://expense.akibworks.in/register.php">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <meta property="og:title" content="Register - Expense Tracker">
    <meta property="og:description" content="Create a free account and start tracking your expenses today.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expense.akibworks.in/register.php">
    <meta property="og:image" content="https://expense.akibworks.in/images/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Register</h1>
            <a href="index.php" class="btn btn-secondary">← Back to Expenses</a>
        </header>

        <section class="form-section">
            <h2>Create Account</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label for="registerUsername">Username:</label>
                    <input type="text" id="registerUsername" required minlength="3">
                    <small>At least 3 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="registerPassword">Password:</label>
                    <div class="password-input-group">
                        <input type="password" id="registerPassword" required minlength="6" onclick="handlePasswordClick(event, 'registerPassword')">
                    </div>
                    <small>At least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <div class="password-input-group">
                        <input type="password" id="confirmPassword" required minlength="6" onclick="handlePasswordClick(event, 'confirmPassword')">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </section>

        <div id="loading" class="loading" style="display: none;">
            Registering...
        </div>

        <div id="errorMessage" class="error-message" style="display: none;"></div>
        <div id="successMessage" class="success-message" style="display: none;"></div>
    </div>

    <script>
        // Password toggle function
        function handlePasswordClick(event, inputId) {
            const input = event.target;
            const rect = input.getBoundingClientRect();
            const iconAreaStart = rect.width - 40; // Icon area starts 40px from the right
            
            // Check if click is in the icon area (right 40px)
            if (event.clientX - rect.left >= iconAreaStart) {
                togglePassword(inputId);
            }
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            
            input.type = isPassword ? 'text' : 'password';
            
            // Toggle CSS class for background icon
            if (isPassword) {
                input.classList.add('show-password');
            } else {
                input.classList.remove('show-password');
            }
        }

        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('registerUsername').value.trim();
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                document.getElementById('errorMessage').textContent = 'Passwords do not match';
                document.getElementById('errorMessage').style.display = 'block';
                return;
            }
            
            document.getElementById('loading').style.display = 'block';
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';
            
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
                    document.getElementById('successMessage').textContent = data.message;
                    document.getElementById('successMessage').style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    document.getElementById('errorMessage').textContent = data.error;
                    document.getElementById('errorMessage').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('errorMessage').textContent = 'Registration failed: ' + error.message;
                document.getElementById('errorMessage').style.display = 'block';
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        });
    </script>
</body>
</html>
