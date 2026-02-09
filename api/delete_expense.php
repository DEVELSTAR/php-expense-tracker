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
    exit();
}

try {
    // Get expense ID from URL
    $expense_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($expense_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid expense ID']);
        exit();
    }
    
    // Get logged-in user ID
    $user_id = $_SESSION['user_id']; // null for guest
    
    // Check if expense exists and belongs to user
    $sql = "SELECT id FROM expenses WHERE id = ?";
    $params = [$expense_id];
    
    // Add user filter (guest can only delete guest expenses, logged-in users can only delete their own)
    if ($user_id === null) {
        // Guest mode: only delete expenses with user_id IS NULL
        $sql .= " AND user_id IS NULL";
    } else {
        // Logged-in mode: only delete expenses for this user
        $sql .= " AND user_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Expense not found']);
        exit();
    }
    
    // Delete the expense
    $delete_sql = "DELETE FROM expenses WHERE id = ?";
    $delete_params = [$expense_id];
    
    // Add user filter for delete (same logic as above)
    if ($user_id === null) {
        $delete_sql .= " AND user_id IS NULL";
    } else {
        $delete_sql .= " AND user_id = ?";
        $delete_params[] = $user_id;
    }
    
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->execute($delete_params);
    
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
