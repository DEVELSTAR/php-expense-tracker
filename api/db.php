<?php
header('Content-Type: application/json');

$host = "localhost";
$dbname = "akibwork_expense";
$username = "akibwork_expuser";
$password = "Saniya1@123";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(["status" => "connected"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "DB connection failed"]);
}
