<?php
require_once 'api/db.php';

header('Content-Type: text/plain');

echo "User Sync Tool\n";
echo "===============\n\n";

// Load users from JSON file
$usersFile = __DIR__ . '/users.json';
$users = [];

if (file_exists($usersFile)) {
    $json = file_get_contents($usersFile);
    $users = json_decode($json, true) ?: [];
}

echo "Found " . count($users) . " users in JSON file\n\n";

$synced = 0;
$errors = 0;

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
            echo "✓ Added user: $username (ID: {$userData['id']})\n";
            $synced++;
        } else {
            echo "- User already exists: $username\n";
        }
    } catch(PDOException $e) {
        echo "✗ Error syncing user $username: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\nSync completed!\n";
echo "Synced: $synced new users\n";
echo "Errors: $errors\n";

if ($synced > 0) {
    echo "\nUsers have been synced to the database. You should now be able to add categories.\n";
    echo "You can delete this file after running it successfully.\n";
}
?>
