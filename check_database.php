<?php
// Temporary file to check and fix database schema
require 'includes/db.php';

echo "<h2>Database Schema Check</h2>";

try {
    // Check if status column exists
    $stmt = $pdo->query("DESCRIBE found_items");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Current Columns in found_items table:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>$column</li>";
    }
    echo "</ul>";
    
    if (!in_array('status', $columns)) {
        echo "<h3 style='color: red;'>Status column is missing! Adding it now...</h3>";
        
        // Add status column
        $pdo->exec("ALTER TABLE found_items ADD COLUMN status ENUM('active', 'claimed', 'removed') DEFAULT 'active'");
        
        // Update existing records
        $pdo->exec("UPDATE found_items SET status = 'active' WHERE status IS NULL");
        
        echo "<h3 style='color: green;'>Status column added successfully!</h3>";
        
        // Check again
        $stmt = $pdo->query("DESCRIBE found_items");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Updated Columns:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>$column</li>";
        }
        echo "</ul>";
    } else {
        echo "<h3 style='color: green;'>Status column exists!</h3>";
    }
    
    // Show sample data
    $stmt = $pdo->query("SELECT id, title, status FROM found_items LIMIT 5");
    $items = $stmt->fetchAll();
    
    echo "<h3>Sample Items with Status:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th></tr>";
    foreach ($items as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['id']) . "</td>";
        echo "<td>" . htmlspecialchars($item['title']) . "</td>";
        echo "<td>" . htmlspecialchars($item['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Database is ready!</strong> You can now delete this file (check_database.php) and use the application normally.</p>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='dashboard.php'>Go to Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
?>
