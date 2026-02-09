<?php
session_start();

// Simple file-based storage for demo
$dataFile = __DIR__ . '/expenses.json';

// Read expenses from file
$expenses = [];
if (file_exists($dataFile)) {
    $json = file_get_contents($dataFile);
    $expenses = json_decode($json, true) ?: [];
}

// Get filters
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';

// Filter expenses based on user session
$filteredExpenses = [];
$totalSum = 0;

foreach ($expenses as $expense) {
    // Filter by user
    $userId = $_SESSION['user_id'] ?? null;
    $isGuest = $_SESSION['is_guest'] ?? false;
    
    if ($isGuest) {
        // Guest sees only guest expenses (user_id is null or 'guest')
        if ($expense['user_id'] !== null && $expense['user_id'] !== 'guest') {
            continue;
        }
    } elseif ($userId) {
        // Logged-in user sees only their expenses
        if ($expense['user_id'] != $userId) {
            continue;
        }
    } else {
        // Not logged in, show guest expenses
        if ($expense['user_id'] !== null && $expense['user_id'] !== 'guest') {
            continue;
        }
    }
    
    // Apply category filter
    if ($category && $expense['category'] !== $category) {
        continue;
    }
    
    // Apply date filter
    if ($date && $expense['expense_date'] !== $date) {
        continue;
    }
    
    $filteredExpenses[] = $expense;
    $totalSum += floatval($expense['total']);
}

// Sort by date (newest first)
usort($filteredExpenses, function($a, $b) {
    return strtotime($b['expense_date']) - strtotime($a['expense_date']);
});

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'expenses' => $filteredExpenses,
    'total_sum' => $totalSum
]);
?>
