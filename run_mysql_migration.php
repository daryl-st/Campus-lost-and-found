<?php
// Simple PHP script to run the MySQL migration
require 'includes/db.php';

echo "<h2>🔧 MySQL Database Migration</h2>";

try {
    // Check if status column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM found_items LIKE 'status'");
    
    if ($stmt->rowCount() == 0) {
        echo "<p>❌ Status column not found. Adding it now...</p>";
        
        // Add status column
        $pdo->exec("ALTER TABLE found_items ADD COLUMN status ENUM('active', 'claimed', 'removed') DEFAULT 'active'");
        echo "<p>✅ Status column added successfully!</p>";
        
        // Update existing records
        $pdo->exec("UPDATE found_items SET status = 'active' WHERE status IS NULL");
        echo "<p>✅ Existing records updated to 'active' status!</p>";
        
    } else {
        echo "<p>✅ Status column already exists!</p>";
    }
    
    // Show table structure
    echo "<h3>📋 Current Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE found_items");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data with status
    echo "<h3>📊 Sample Items with Status:</h3>";
    $stmt = $pdo->query("SELECT id, title, status FROM found_items LIMIT 5");
    $items = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th></tr>";
    foreach ($items as $item) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['id']) . "</td>";
        echo "<td>" . htmlspecialchars($item['title']) . "</td>";
        echo "<td><span style='color: " . ($item['status'] === 'active' ? 'green' : 'orange') . ";'>●</span> " . htmlspecialchars($item['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #d1fae5; border: 1px solid #10b981; border-radius: 8px; padding: 15px; margin: 20px 0;'>";
    echo "<h3 style='color: #065f46; margin: 0 0 10px 0;'>🎉 Migration Complete!</h3>";
    echo "<p style='color: #047857; margin: 0;'>Your database is now ready. You can delete this file and use the application normally.</p>";
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px;'>🏠 Go to Homepage</a> ";
    echo "<a href='dashboard.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; margin-left: 10px;'>📊 Go to Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='background: #fee2e2; border: 1px solid #ef4444; border-radius: 8px; padding: 15px; margin: 20px 0;'>";
    echo "<h3 style='color: #dc2626; margin: 0 0 10px 0;'>❌ Error</h3>";
    echo "<p style='color: #b91c1c; margin: 0;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
