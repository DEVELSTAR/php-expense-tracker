<?php
require_once 'api/db.php';

// Load users from JSON file
$usersFile = __DIR__ . '/users.json';
$users = [];

if (file_exists($usersFile)) {
    $json = file_get_contents($usersFile);
    $users = json_decode($json, true) ?: [];
}

echo "Found " . count($users) . " users in JSON file\n";

// Sync users to database
foreach ($users as $username => $userData) {
    try {
        // Check if user already exists in database
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
        
        if (!$checkStmt->fetch()) {
            // Insert user into database
            $insertStmt = $conn->prepare("INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)");
            $insertStmt->execute([$userData['id'], $username, $userData['password']]);
            echo "Added user: $username (ID: {$userData['id']})\n";
        } else {
            echo "User already exists: $username\n";
        }
    } catch(PDOException $e) {
        echo "Error syncing user $username: " . $e->getMessage() . "\n";
    }
}

echo "User sync completed!\n";
?>
