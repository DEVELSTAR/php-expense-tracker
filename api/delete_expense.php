<?php
require_once 'db.php';

// Only allow DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get expense ID from URL parameter or request body
$expense_id = null;

// Try to get ID from URL parameter (for DELETE /api/delete_expense.php?id=123)
if (isset($_GET['id'])) {
    $expense_id = intval($_GET['id']);
}

// Try to get ID from JSON body (for DELETE /api/delete_expense.php with JSON payload)
if ($expense_id === null) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'])) {
        $expense_id = intval($data['id']);
    }
}

// Validate expense ID
if (!$expense_id || $expense_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing expense ID']);
    exit();
}

try {
    // Check if expense exists
    $check_stmt = $conn->prepare("SELECT id FROM expenses WHERE id = ?");
    $check_stmt->execute([$expense_id]);
    $existing = $check_stmt->fetch();
    
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Expense not found']);
        exit();
    }
    
    // Delete expense
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$expense_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Expense deleted successfully',
        'deleted_id' => $expense_id
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete expense: ' . $e->getMessage()]);
}
?>
