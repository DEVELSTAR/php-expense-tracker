<?php
session_start();

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);

header('Content-Type: application/json');
echo json_encode([
    'logged_in' => $logged_in,
    'is_guest' => $logged_in && isset($_SESSION['is_guest']) ? $_SESSION['is_guest'] : false,
    'username' => $logged_in ? $_SESSION['username'] : null
]);
?>
