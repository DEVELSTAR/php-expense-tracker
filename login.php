<?php
session_start();

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    if ($username === 'guest') {
        // Guest login
        $_SESSION['user_id'] = 'guest';
        $_SESSION['username'] = 'Guest';
        $_SESSION['is_guest'] = true;
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Logged in as Guest',
            'user' => ['id' => 'guest', 'username' => 'Guest']
        ]);
        exit;
    }
    
    // Simple user validation (in production, use proper password hashing)
    $usersFile = __DIR__ . '/users.json';
    $users = [];
    
    if (file_exists($usersFile)) {
        $json = file_get_contents($usersFile);
        $users = json_decode($json, true) ?: [];
    }
    
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['user_id'] = $users[$username]['id'];
        $_SESSION['username'] = $users[$username]['username'];
        $_SESSION['is_guest'] = false;
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => $users[$username]
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid username or password'
        ]);
    }
    exit;
}

// Show login page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to Your Expense Tracker Account | Akibworks</title>
    <meta name="description" content="Login to your Akibworks Expense Tracker account to manage your personal expenses and budgets.">
    <meta name="keywords" content="expense tracker login, account login, expense management">
    <meta name="author" content="Akibworks">
    <meta name="theme-color" content="#2563eb">
    <link rel="canonical" href="https://expense.akibworks.in/login.php">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <meta property="og:title" content="Login - Expense Tracker">
    <meta property="og:description" content="Securely login to manage your personal expenses.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://expense.akibworks.in/login.php">
    <meta property="og:image" content="https://expense.akibworks.in/images/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Login</h1>
            <a href="index.php" class="btn btn-secondary">← Back to Expenses</a>
        </header>

        <section class="form-section">
            <h2>Login to Your Account</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="loginUsername">Username:</label>
                    <input type="text" id="loginUsername" required>
                </div>
                
                <div class="form-group">
                    <label for="loginPassword">Password:</label>
                    <div class="password-input-group">
                        <input type="password" id="loginPassword" required onclick="handlePasswordClick(event, 'loginPassword')">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
                
                <p class="auth-note">
                    <small>Guest login: username "guest" with any password</small>
                </p>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </section>

        <div id="loading" class="loading" style="display: none;">
            Logging in...
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

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('loginUsername').value.trim();
            const password = document.getElementById('loginPassword').value;
            
            document.getElementById('loading').style.display = 'block';
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('successMessage').style.display = 'none';
            
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
                document.getElementById('errorMessage').textContent = 'Login failed: ' + error.message;
                document.getElementById('errorMessage').style.display = 'block';
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        });
    </script>
</body>
</html>
