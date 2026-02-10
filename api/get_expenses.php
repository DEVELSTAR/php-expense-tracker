<?php
require_once 'db.php';
session_start();

// Get filters
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';

// Get user info
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

try {
    // Build the base query
    $query = "SELECT * FROM expenses WHERE 1=1";
    $params = [];
    
    // Filter by user
    if ($isGuest || !$userId) {
        // Guests see only guest expenses (user_id IS NULL)
        $query .= " AND user_id IS NULL";
    } else {
        // Logged-in users see only their expenses
        $query .= " AND user_id = ?";
        $params[] = $userId;
    }
    
    // Apply category filter
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    // Apply date filter
    if ($date) {
        $query .= " AND expense_date = ?";
        $params[] = $date;
    }
    
    // Order by date and time (newest first)
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();
    
    // Calculate total sum
    $totalSum = 0;
    foreach ($expenses as $expense) {
        $totalSum += floatval($expense['total']);
    }
    
    echo json_encode([
        'success' => true,
        'expenses' => $expenses,
        'total_sum' => $totalSum
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
