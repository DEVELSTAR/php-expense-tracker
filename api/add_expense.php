<?php
session_start();

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Validate required fields
if (empty($data['expense_date']) || empty($data['total']) || empty($data['category'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
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

// Add user info based on session
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

if ($isGuest) {
    $data['user_id'] = 'guest';
} elseif ($userId) {
    $data['user_id'] = $userId;
} else {
    $data['user_id'] = 'guest';
}

// Create new expense
$newExpense = [
    'id' => time() + rand(1000, 9999), // Simple unique ID
    'expense_date' => $data['expense_date'],
    'total' => $data['total'],
    'category' => $data['category'],
    'message' => $data['message'] ?? '',
    'user_id' => $data['user_id'],
    'created_at' => date('Y-m-d H:i:s')
];

// Add to expenses array
$expenses[] = $newExpense;

// Save to file
file_put_contents($dataFile, json_encode($expenses, JSON_PRETTY_PRINT));

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Expense added successfully',
    'expense' => $newExpense
]);
?>
