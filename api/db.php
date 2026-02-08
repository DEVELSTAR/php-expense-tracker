<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration (use cPanel values)
$host = "localhost";
$dbname = "akibwork_expense";      // your DB name
$username = "akibwork_expuser";    // your DB user
$password = "Saniya1@123";    // your real password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}
?>
