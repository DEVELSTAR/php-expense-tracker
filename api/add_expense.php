<?php
require_once 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['expense_date']) || !isset($data['total']) || !isset($data['category'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

// Validate data
$expense_date = $data['expense_date'];
$total = floatval($data['total']);
$category = $data['category'];
$message = isset($data['message']) ? trim($data['message']) : null;

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $expense_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit();
}

// Validate total amount
if ($total <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Total amount must be greater than 0']);
    exit();
}

// Validate category
$allowed_categories = ['Both', 'Domestic', 'Akib', 'Saniya', 'Neha', 'Family'];
if (!in_array($category, $allowed_categories)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid category']);
    exit();
}

try {
    // Insert expense
    $stmt = $conn->prepare("INSERT INTO expenses (expense_date, total, category, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$expense_date, $total, $category, $message]);
    
    // Get the inserted record
    $id = $conn->lastInsertId();
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    $expense = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Expense added successfully',
        'expense' => $expense
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add expense: ' . $e->getMessage()]);
}
?>
