<?php
require_once 'db.php';
session_start();

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || empty($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Category ID is required']);
    exit;
}

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

// Guests cannot delete categories
if ($isGuest || !$userId) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to delete categories']);
    exit;
}

try {
    // First check if category belongs to the user
    $checkStmt = $conn->prepare("SELECT id, user_id FROM categories WHERE id = ?");
    $checkStmt->execute([$data['id']]);
    $category = $checkStmt->fetch();
    
    if (!$category) {
        echo json_encode(['success' => false, 'error' => 'Category not found']);
        exit;
    }
    
    // Users can only delete their own categories, not default ones (user_id = NULL)
    if ($category['user_id'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'You can only delete your own categories']);
        exit;
    }
    
    // Check if category is being used by any expenses
    $usageStmt = $conn->prepare("SELECT COUNT(*) as count FROM expenses WHERE category = (SELECT name FROM categories WHERE id = ?) AND user_id = ?");
    $usageStmt->execute([$data['id'], $userId]);
    $usage = $usageStmt->fetch();
    
    if ($usage['count'] > 0) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete category that is in use by expenses']);
        exit;
    }
    
    // Delete the category
    $deleteStmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $deleteStmt->execute([$data['id'], $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Category deleted successfully'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
