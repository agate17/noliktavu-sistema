<?php
include 'config.php';

try {
    // Check if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() == 0) {
        echo "Products table does not exist!";
        exit;
    }

    // Get table structure
    $stmt = $pdo->query("DESCRIBE products");
    echo "<h2>Products Table Structure:</h2>";
    echo "<pre>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";

    // Get sample data
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    echo "<h2>Sample Product Data:</h2>";
    echo "<pre>";
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 