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
if (empty($data['expense_date']) || empty($data['total']) || empty($data['category'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Get user info
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

// Set user_id based on session
$expenseUserId = null;
if ($isGuest) {
    $expenseUserId = null; // Guest expenses have NULL user_id
} elseif ($userId) {
    $expenseUserId = $userId;
} else {
    $expenseUserId = null; // Default to guest if not logged in
}

try {
    // Validate that the category exists and is accessible to the user
    if ($isGuest || !$userId) {
        // Guests can only use default categories
        $categoryStmt = $conn->prepare("SELECT name FROM categories WHERE name = ? AND user_id IS NULL");
        $categoryStmt->execute([$data['category']]);
    } else {
        // Logged-in users can use default categories or their own
        $categoryStmt = $conn->prepare("SELECT name FROM categories WHERE name = ? AND (user_id IS NULL OR user_id = ?)");
        $categoryStmt->execute([$data['category'], $userId]);
    }
    
    if (!$categoryStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Invalid category']);
        exit;
    }
    
    // Insert expense
    $stmt = $conn->prepare("
        INSERT INTO expenses (expense_date, total, category, message, user_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['expense_date'],
        $data['total'],
        $data['category'],
        $data['message'] ?? null,
        $expenseUserId
    ]);
    
    $expenseId = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Expense added successfully',
        'expense' => [
            'id' => $expenseId,
            'expense_date' => $data['expense_date'],
            'total' => $data['total'],
            'category' => $data['category'],
            'message' => $data['message'] ?? null,
            'user_id' => $expenseUserId,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
