<?php
require_once 'db.php';
session_start();

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Validate required fields
if (empty($data['name'])) {
    echo json_encode(['success' => false, 'error' => 'Category name is required']);
    exit;
}

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

// Guests cannot create custom categories, they can only use default ones
if ($isGuest || !$userId) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to create categories']);
    exit;
}

try {
    // Check if category already exists for this user
    $checkStmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?");
    $checkStmt->execute([$data['name'], $userId]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Category already exists']);
        exit;
    }
    
    // Create new category
    $stmt = $conn->prepare("INSERT INTO categories (name, user_id) VALUES (?, ?)");
    $stmt->execute([$data['name'], $userId]);
    
    $categoryId = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Category created successfully',
        'category' => [
            'id' => $categoryId,
            'name' => $data['name'],
            'user_id' => $userId
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
