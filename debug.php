<?php
// PHP Debug Information
echo "<h1>PHP Debug Information</h1>";

// Check if PHP is working
echo "<h2>PHP Version:</h2>";
echo phpversion();

// Check session support
echo "<h2>Session Support:</h2>";
if (function_exists('session_start')) {
    echo "✅ Session functions available<br>";
    session_start();
    echo "✅ Session started successfully<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session functions not available<br>";
}

// Check file permissions
echo "<h2>File Permissions:</h2>";
echo "Current file: " . __FILE__ . "<br>";
echo "Current directory: " . __DIR__ . "<br>";

// Check if we can write files
echo "<h2>Write Test:</h2>";
$test_file = __DIR__ . '/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✅ File writing works<br>";
    unlink($test_file); // Clean up
} else {
    echo "❌ File writing failed<br>";
}

// Check database connection
echo "<h2>Database Connection:</h2>";
try {
    require_once 'api/db.php';
    echo "✅ Database file loaded<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Show all loaded extensions
echo "<h2>Loaded Extensions:</h2>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    echo "- " . $ext . "<br>";
}

// Show server info
echo "<h2>Server Info:</h2>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?>
