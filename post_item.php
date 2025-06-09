<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $datetime = $_POST['found_datetime'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    if ($image['error'] === 0) {
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $image_path = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($image['tmp_name'], $image_path);
    } else {
        $errors[] = "Image upload failed.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO found_items (user_id, title, category, location, found_datetime, description, image_path)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $category, $location, $datetime, $description, $image_path]);

        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Found Item</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to style.css from earlier -->
</head>
<body>
    <header>
    <a href="index.php" ><div class="logo">Campus Lost & Found</div></a>
    

        <div class="actions">
            <span class="username">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </header>

    <main>
        <form class="post-form" method="POST" enctype="multipart/form-data">
            <h2>Post Found Item</h2>
            <p class="subtitle">Help someone find their lost item</p>

            <?php if (!empty($errors)): ?>
                <div style="color: red; margin-bottom: 10px;">
                    <?= implode("<br>", $errors) ?>
                </div>
            <?php endif; ?>

            <label for="title">Item Title *</label>
            <input type="text" name="title" id="title" placeholder="e.g., iPhone 13 Pro, Blue Backpack" required>

            <label for="category">Category</label>
            <select name="category" id="category">
                <option>Electronics</option>
                <option>Clothing</option>
                <option>Accessories</option>
                <option>Documents</option>
                <option>Others</option>
            </select>

            <label for="location">Found Location *</label>
            <input type="text" name="location" id="location" placeholder="e.g., Library, Cafeteria" required>

            <label for="found_datetime">Found Date *</label>
            <input type="datetime-local" name="found_datetime" id="found_datetime" required>

            <label for="description">Description</label>
            <textarea name="description" id="description" placeholder="Provide any additional details about the item..." required></textarea>

            <label for="image">Item Photo</label>
            <div class="upload-box" onclick="document.getElementById('image').click();">
                <input type="file" name="image" id="image" accept="image/png, image/jpeg" required>
                <p>Click to upload or drag and drop<br><small>PNG, JPG (MAX. 5MB)</small></p>
            </div>

            <div class="actions">
                <a href="index.php" class="cancel-btn">Cancel</a>
                <button type="submit" class="submit-btn">Post Item</button>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('image').addEventListener('change', function () {
            if (this.files[0].size > 5 * 1024 * 1024) {
                alert('File is too large. Max size is 5MB.');
                this.value = '';
            }
        });
    </script>
</body>
</html>
