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
    // First, get the item to check ownership and get image path
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['error_message'] = "Item not found or you don't have permission to delete it.";
        header("Location: dashboard.php");
        exit();
    }

    // Delete the image file if it exists
    if (!empty($item['image_path']) && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }

    // Delete the item from database
    $stmt = $pdo->prepare("DELETE FROM found_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);

    $_SESSION['success_message'] = "Item deleted successfully!";
    header("Location: dashboard.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error deleting item: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}
?>
