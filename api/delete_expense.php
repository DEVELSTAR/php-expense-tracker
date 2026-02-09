<?php
session_start();

// Get expense ID
$expenseId = $_GET['id'] ?? null;

if (!$expenseId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Expense ID required']);
    exit;
}

// Simple file-based storage
$dataFile = __DIR__ . '/expenses.json';

// Read existing expenses
$expenses = [];
if (file_exists($dataFile)) {
    $json = file_get_contents($dataFile);
    $expenses = json_decode($json, true) ?: [];
}

// Find and delete expense
$found = false;
$updatedExpenses = [];

foreach ($expenses as $expense) {
    if ($expense['id'] == $expenseId) {
        // Check if user can delete this expense
        $userId = $_SESSION['user_id'] ?? null;
        $isGuest = $_SESSION['is_guest'] ?? false;
        
        if ($isGuest) {
            // Guest can delete guest expenses
            if ($expense['user_id'] !== null && $expense['user_id'] !== 'guest') {
                $updatedExpenses[] = $expense; // Keep it, can't delete
                continue;
            }
        } elseif ($userId) {
            // Logged-in user can delete their own expenses
            if ($expense['user_id'] != $userId) {
                $updatedExpenses[] = $expense; // Keep it, can't delete
                continue;
            }
        } else {
            // Not logged in, can only delete guest expenses
            if ($expense['user_id'] !== null && $expense['user_id'] !== 'guest') {
                $updatedExpenses[] = $expense; // Keep it, can't delete
                continue;
            }
        }
        
        $found = true; // Don't add this expense to updated array (delete it)
    } else {
        $updatedExpenses[] = $expense; // Keep this expense
    }
}

if ($found) {
    // Save updated expenses
    file_put_contents($dataFile, json_encode($updatedExpenses, JSON_PRETTY_PRINT));
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Expense deleted successfully'
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Expense not found or cannot be deleted'
    ]);
}
?>
