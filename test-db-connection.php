<?php
// Database connection configuration
$db_host = "localhost";
$db_name = "gusturi_romanesti";
$db_user = "root";
$db_pass = "";

// Establish database connection
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h1>Database Connection Successful!</h1>";
    
    // Test query to check if tables exist
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Database Tables:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check if there are any products
    $stmt = $conn->query("SELECT COUNT(*) FROM produse");
    $productCount = $stmt->fetchColumn();
    
    echo "<h2>Product Count: $productCount</h2>";
    
    if ($productCount > 0) {
        // Show a sample product
        $stmt = $conn->query("SELECT * FROM produse LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h2>Sample Product:</h2>";
        echo "<pre>";
        print_r($product);
        echo "</pre>";
    }
    
} catch(PDOException $e) {
    echo "<h1>Database Connection Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>