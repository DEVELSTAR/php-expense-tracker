<?php
require_once 'db.php';
session_start();

// Get user info
$userId = $_SESSION['user_id'] ?? null;
$isGuest = $_SESSION['is_guest'] ?? false;

try {
    $categories = [];
    
    if ($isGuest || !$userId) {
        // Guests get default categories (user_id IS NULL)
        $stmt = $conn->prepare("SELECT id, name, user_id FROM categories WHERE user_id IS NULL ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    } else {
        // Logged-in users get default categories + their own categories
        $stmt = $conn->prepare("
            SELECT id, name, user_id FROM categories 
            WHERE user_id IS NULL OR user_id = ? 
            ORDER BY user_id, name
        ");
        $stmt->execute([$userId]);
        $categories = $stmt->fetchAll();
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
