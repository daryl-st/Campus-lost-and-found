<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $location = trim($_POST['location']);
    $datetime = $_POST['found_datetime'];
    $description = trim($_POST['description']);
    $image = $_FILES['image'];

    // Validation
    if (empty($title)) $errors[] = "Item title is required";
    if (empty($category)) $errors[] = "Category is required";
    if (empty($location)) $errors[] = "Location is required";
    if (empty($datetime)) $errors[] = "Date and time found is required";
    if (empty($description)) $errors[] = "Description is required";

    // Handle image upload
    $image_path = null;
    if ($image['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($image['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($image['size'] > $max_size) {
            $errors[] = "Image size must be less than 5MB";
        } else {
            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $image_path = 'uploads/' . $filename;

            if (!move_uploaded_file($image['tmp_name'], $image_path)) {
                $errors[] = "Failed to upload image";
                $image_path = null;
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO found_items (user_id, title, category, location, found_datetime, description, image_path, created_at)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user_id'], $title, $category, $location, $datetime, $description, $image_path]);

            $success = "Item posted successfully!";
            // Clear form data
            $_POST = [];
            header("Location: dashboard.php");
        exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Found Item | Campus Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8fafc;
            background-image: radial-gradient(circle at 25px 25px, rgba(79, 70, 229, 0.15) 2%, transparent 0%),
                radial-gradient(circle at 75px 75px, rgba(79, 70, 229, 0.1) 2%, transparent 0%);
            background-size: 100px 100px;
            color: #1e293b;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        header {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-icon {
            font-size: 1.8rem;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .username {
            font-weight: 500;
            color: #64748b;
        }

        .logout {
            color: #64748b;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .logout:hover {
            background-color: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }

        /* Main Content */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2.2rem;
            color: #1e293b;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 1.1rem;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .error-msg {
            background: #fed7d7;
            color: #c53030;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            border-left: 4px solid #e53e3e;
        }

        .success-msg {
            background: #c6f6d5;
            color: #2f855a;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            border-left: 4px solid #38a169;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .required {
            color: #e53e3e;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .upload-section {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8fafc;
        }

        .upload-section:hover {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.05);
        }

        .upload-section.dragover {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.1);
        }

        .upload-icon {
            font-size: 3rem;
            color: #94a3b8;
            margin-bottom: 15px;
        }

        .upload-text {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .upload-hint {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .file-input {
            display: none;
        }

        .image-preview {
            margin-top: 15px;
            text-align: center;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .remove-image {
            display: inline-block;
            margin-top: 10px;
            color: #e53e3e;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #475569;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .actions {
                width: 100%;
                justify-content: space-between;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 30px 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .page-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .page-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">🔍</span> Campus Lost & Found
            </a>
            <div class="actions">
                <span class="username">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="dashboard.php" class="logout">Dashboard</a>
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h1 class="page-title">Post Found Item</h1>
            <p class="page-subtitle">Help someone find their lost belongings</p>
        </div>

        <div class="form-card">
            <?php if (!empty($errors)): ?>
                <div class="error-msg">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-msg">
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label">Item Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="e.g., iPhone 13 Pro, Blue Backpack" required 
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="category" class="form-label">Category <span class="required">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select a category</option>
                            <option value="Electronics" <?= ($_POST['category'] ?? '') === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                            <option value="Clothing" <?= ($_POST['category'] ?? '') === 'Clothing' ? 'selected' : '' ?>>Clothing</option>
                            <option value="Accessories" <?= ($_POST['category'] ?? '') === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
                            <option value="Documents" <?= ($_POST['category'] ?? '') === 'Documents' ? 'selected' : '' ?>>Documents</option>
                            <option value="Books" <?= ($_POST['category'] ?? '') === 'Books' ? 'selected' : '' ?>>Books</option>
                            <option value="Keys" <?= ($_POST['category'] ?? '') === 'Keys' ? 'selected' : '' ?>>Keys</option>
                            <option value="Others" <?= ($_POST['category'] ?? '') === 'Others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location" class="form-label">Found Location <span class="required">*</span></label>
                        <input type="text" id="location" name="location" class="form-input" 
                               placeholder="e.g., Library, Cafeteria, Building A" required 
                               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="found_datetime" class="form-label">Date & Time Found <span class="required">*</span></label>
                        <input type="datetime-local" id="found_datetime" name="found_datetime" class="form-input" required 
                               value="<?= htmlspecialchars($_POST['found_datetime'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" class="form-textarea" 
                              placeholder="Provide detailed description of the item, including color, brand, condition, and any distinguishing features..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Item Photo</label>
                    <div class="upload-section" onclick="document.getElementById('image').click();">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Click to upload or drag and drop</div>
                        <div class="upload-hint">PNG, JPG, GIF (MAX. 5MB)</div>
                        <input type="file" id="image" name="image" class="file-input" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div id="image-preview" class="image-preview" style="display: none;">
                        <img id="preview-img" class="preview-image" src="/placeholder.svg" alt="Preview">
                        <br>
                        <a href="#" class="remove-image" onclick="removeImage()">Remove Image</a>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Post Item</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage() {
            document.getElementById('image').value = '';
            document.getElementById('image-preview').style.display = 'none';
        }

        // Drag and drop functionality
        const uploadSection = document.querySelector('.upload-section');
        
        uploadSection.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadSection.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        uploadSection.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('image').files = files;
                previewImage(document.getElementById('image'));
            }
        });
    </script>
</body>
</html>
