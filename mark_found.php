<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'includes/db.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: dashboard.php");
    exit();
}

try {
    // Check if item exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error_message'] = "Item not found or you don't have permission to modify it.";
        header("Location: dashboard.php");
        exit();
    }

    // Check if status column exists, if not add it
    $stmt = $pdo->query("SHOW COLUMNS FROM found_items LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        // Add status column if it doesn't exist
        $pdo->exec("ALTER TABLE found_items ADD COLUMN status ENUM('active', 'claimed', 'removed') DEFAULT 'active'");
        $pdo->exec("UPDATE found_items SET status = 'active' WHERE status IS NULL");
    }

    // Update item status to 'claimed'
    $stmt = $pdo->prepare("UPDATE found_items SET status = 'claimed', updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);

    $_SESSION['success_message'] = "Item marked as found! It has been removed from active listings.";
    header("Location: dashboard.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error updating item: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}
?>
