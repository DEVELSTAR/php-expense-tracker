<?php
require_once 'api/db.php';

echo "<h2>Debug API Test</h2>";

try {
    // Test 1: Check if table exists and has data
    echo "<h3>1. Table Check</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'expenses'");
    $table_exists = $stmt->rowCount() > 0;
    echo "Table exists: " . ($table_exists ? "YES" : "NO") . "<br>";
    
    if ($table_exists) {
        // Test 2: Count all expenses
        echo "<h3>2. Count All Expenses</h3>";
        $stmt = $conn->query("SELECT COUNT(*) as count FROM expenses");
        $result = $stmt->fetch();
        echo "Total expenses: " . $result['count'] . "<br>";
        
        // Test 3: Get total sum
        echo "<h3>3. Total Sum</h3>";
        $stmt = $conn->query("SELECT SUM(total) as total_sum FROM expenses");
        $result = $stmt->fetch();
        echo "Total sum: " . $result['total_sum'] . "<br>";
        
        // Test 4: Show sample data
        echo "<h3>4. Sample Data</h3>";
        $stmt = $conn->query("SELECT * FROM expenses LIMIT 5");
        $expenses = $stmt->fetchAll();
        
        if (count($expenses) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Date</th><th>Total</th><th>Category</th></tr>";
            foreach ($expenses as $expense) {
                echo "<tr>";
                echo "<td>{$expense['id']}</td>";
                echo "<td>{$expense['expense_date']}</td>";
                echo "<td>{$expense['total']}</td>";
                echo "<td>{$expense['category']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No expenses found in table.<br>";
        }
    }
    
    // Test 5: Test get_expenses.php API
    echo "<h3>5. API Test</h3>";
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/get_expenses.php';
    echo "API URL: " . $api_url . "<br>";
    
    $response = file_get_contents($api_url);
    echo "API Response: <pre>" . htmlspecialchars($response) . "</pre>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
