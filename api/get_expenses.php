<?php
require_once 'db.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Build query based on category filter
    $sql = "SELECT * FROM expenses";
    $params = [];
    
    // Check if category filter is provided
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $category = $_GET['category'];
        $sql .= " WHERE category = ?";
        $params[] = $category;
    }
    
    // Order by date descending (newest first)
    $sql .= " ORDER BY expense_date DESC, created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();
    
    // Calculate total sum
    $total_sql = "SELECT SUM(total) as total_sum FROM expenses";
    $total_params = [];
    
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $total_sql .= " WHERE category = ?";
        $total_params[] = $category;
    }
    
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute($total_params);
    $total_result = $total_stmt->fetch();
    $total_sum = $total_result['total_sum'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'expenses' => $expenses,
        'total_sum' => floatval($total_sum),
        'count' => count($expenses)
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch expenses: ' . $e->getMessage()]);
}
?>
