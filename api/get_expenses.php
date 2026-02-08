<?php
require_once 'db.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get category filter if exists
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    // Get date filter if exists
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    
    // Build query based on filters
    $sql = "SELECT * FROM expenses";
    $params = [];
    $where_clauses = [];
    
    // Check if category filter is provided
    if ($category !== '') {
        $where_clauses[] = "category = ?";
        $params[] = $category;
    }
    
    // Check if date filter is provided
    if ($date !== '') {
        $where_clauses[] = "expense_date = ?";
        $params[] = $date;
    }
    
    // Add WHERE clause if any filters exist
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }
    
    // Order by date descending (newest first)
    $sql .= " ORDER BY expense_date DESC, created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();
    
    // Calculate total sum with same filters
    $total_sql = "SELECT SUM(total) as total_sum FROM expenses";
    $total_params = [];
    $total_where_clauses = [];
    
    if ($category !== '') {
        $total_where_clauses[] = "category = ?";
        $total_params[] = $category;
    }
    
    if ($date !== '') {
        $total_where_clauses[] = "expense_date = ?";
        $total_params[] = $date;
    }
    
    if (!empty($total_where_clauses)) {
        $total_sql .= " WHERE " . implode(' AND ', $total_where_clauses);
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
