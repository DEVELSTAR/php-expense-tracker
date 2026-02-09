<?php
session_start();
require_once 'api/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        exit();
    }
    
    $username = trim($data['username']);
    $password = $data['password'];
    
    // Special case for guest user
    if ($username === 'guest') {
        // Allow guest login with any password for demo purposes
        $_SESSION['user_id'] = null;
        $_SESSION['username'] = 'Guest';
        $_SESSION['is_guest'] = true;
        
        echo json_encode([
            'success' => true,
            'message' => 'Logged in as Guest',
            'user' => ['id' => null, 'username' => 'Guest']
        ]);
        exit();
    }
    
    // Find user in database
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
        exit();
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_guest'] = false;
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => ['id' => $user['id'], 'username' => $user['username']]
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
}
?>
