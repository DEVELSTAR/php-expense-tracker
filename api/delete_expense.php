<?php
require_once 'db.php';
session_start();

// Get expense ID
$expenseId = $_GET['id'] ?? null;

if (!$expenseId) {
    echo json_encode(['success' => false, 'error' => 'Expense ID required']);
    exit;
}

// Get user info
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

try {
    // First get the expense to check ownership
    $stmt = $conn->prepare("SELECT id, user_id FROM expenses WHERE id = ?");
    $stmt->execute([$expenseId]);
    $expense = $stmt->fetch();
    
    if (!$expense) {
        echo json_encode(['success' => false, 'error' => 'Expense not found']);
        exit;
    }
    
    // Check if user can delete this expense
    if ($isGuest || !$userId) {
        // Guests can delete guest expenses (user_id IS NULL)
        if ($expense['user_id'] !== null) {
            echo json_encode(['success' => false, 'error' => 'You can only delete your own expenses']);
            exit;
        }
    } else {
        // Logged-in users can delete their own expenses
        if ($expense['user_id'] != $userId) {
            echo json_encode(['success' => false, 'error' => 'You can only delete your own expenses']);
            exit;
        }
    }
    
    // Delete the expense
    $deleteStmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $deleteStmt->execute([$expenseId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Expense deleted successfully'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
