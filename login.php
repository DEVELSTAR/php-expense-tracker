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
    <title>Login - Akibworks Expense Tracker</title>
    <link rel="icon" type="image/png" href="images/spending.png">
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
                        <input type="password" id="loginPassword" required onclick="togglePassword('loginPassword')">
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
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            
            input.type = isPassword ? 'text' : 'password';
            
            // Toggle the CSS class for background icon
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
