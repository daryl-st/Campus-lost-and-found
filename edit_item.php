<?php
/******************************************************************
 *  edit_item.php  –  Campus Lost & Found
 *  Lets a logged-in user update any attribute of a found item,
 *  including replacing – or keeping – the photo.
 ******************************************************************/
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'includes/db.php';

$id      = $_GET['id'] ?? 0;
$errors  = [];
$success = '';

// ────────────────────────────────────────────────────────────
// 1.  Fetch the item (and be sure it belongs to this user)
// ────────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        "SELECT * FROM found_items WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {           // item not found OR not owned by user
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit();
}

// ────────────────────────────────────────────────────────────
// 2.  Handle the POST (update) request
// ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* —— gather form data —— */
    $title         = trim($_POST['title'] ?? '');
    $category      = $_POST['category']      ?? '';
    $location      = trim($_POST['location'] ?? '');
    $foundDateTime = $_POST['found_datetime'] ?? '';
    $description   = trim($_POST['description'] ?? '');

    /* —— validation —— */
    if ($title === '')         $errors[] = "Item title is required";
    if ($category === '')      $errors[] = "Category is required";
    if ($location === '')      $errors[] = "Location is required";
    if ($foundDateTime === '') $errors[] = "Date & time found is required";
    if ($description === '')   $errors[] = "Description is required";

    /* —— image upload (optional) —— */
    $image_path = $item['image_path'];               // keep existing by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

        $allowedTypes = ['jpg','jpeg','png','gif','webp'];
        $maxSize      = 5 * 1024 * 1024;             // 5 MB
        $fileSize     = $_FILES['image']['size'];
        $ext          = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedTypes))            $errors[] = "Only JPG, PNG, GIF or WEBP images are allowed";
        elseif ($fileSize > $maxSize)                  $errors[] = "Image must be smaller than 5 MB";

        /* —— move the file —— */
        if (empty($errors)) {
            $uploadDir = __DIR__ . '/uploads/';        // absolute path
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newName    = uniqid('img_', true) . '.' . $ext;
            $targetPath = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {

                // delete previous image (if any & different)
                if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])) {
                    unlink(__DIR__ . '/' . $item['image_path']);
                }

                $image_path = 'uploads/' . $newName;   // save relative path to DB

            } else {
                $errors[] = "Failed to upload image – please try again.";
            }
        }
    }

    /* —— update DB if no errors —— */
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE found_items
                 SET title = ?, category = ?, location = ?, found_datetime = ?,
                     description = ?, image_path = ?, updated_at = NOW()
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([
                $title, $category, $location, $foundDateTime,
                $description, $image_path, $id, $_SESSION['user_id']
            ]);

            $success = "Item updated successfully!";
            // Update local copy so the refreshed form shows new values
            $item['title']          = $title;
            $item['category']       = $category;
            $item['location']       = $location;
            $item['found_datetime'] = $foundDateTime;
            $item['description']    = $description;
            $item['image_path']     = $image_path;

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
<title>Edit Item | Campus Lost & Found</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/*  ←── (same CSS you supplied – unchanged) ─────────────────── */
<?= '/* your long CSS block here …  */' ?>
/*  For brevity in this answer, include the entire CSS block you already have */
</style>
</head>
<body>
<header>
    <div class="header-container">
        <a href="index.php" class="logo"><span class="logo-icon">🔍</span> Campus Lost & Found</a>
        <div class="actions">
            <span class="username">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="dashboard.php" class="logout">Dashboard</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</header>

<main class="container">
    <div class="page-header">
        <h1 class="page-title">Edit Item</h1>
        <p class="page-subtitle">Update your found-item details</p>
    </div>

    <div class="form-card">
        <?php if ($errors): ?>
            <div class="error-msg">
                <?php foreach ($errors as $e) echo '<p>'.htmlspecialchars($e).'</p>'; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-msg"><p><?= htmlspecialchars($success) ?></p></div>
        <?php endif; ?>

        <!-- ── Edit Form ─────────────────────────────────────── -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="title" class="form-label">Item Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-input" required
                           value="<?= htmlspecialchars($item['title']) ?>">
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category <span class="required">*</span></label>
                    <select id="category" name="category" class="form-select" required>
                        <?php
                        $categories = ['Electronics','Clothing','Accessories','Documents','Books','Keys','Others'];
                        foreach ($categories as $c) {
                            $sel = $item['category'] === $c ? 'selected' : '';
                            echo "<option value=\"$c\" $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location" class="form-label">Found Location <span class="required">*</span></label>
                    <input type="text" id="location" name="location" class="form-input" required
                           value="<?= htmlspecialchars($item['location']) ?>">
                </div>

                <div class="form-group">
                    <label for="found_datetime" class="form-label">Date &amp; Time Found <span class="required">*</span></label>
                    <input type="datetime-local" id="found_datetime" name="found_datetime" class="form-input" required
                           value="<?= date('Y-m-d\TH:i', strtotime($item['found_datetime'])) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description <span class="required">*</span></label>
                <textarea id="description" name="description" class="form-textarea" required><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <!-- ── Current image display ─────────────────────── -->
            <?php if ($item['image_path'] && file_exists(__DIR__ . '/' . $item['image_path'])): ?>
                <div class="current-image">
                    <span class="current-image-label">Current Image:</span>
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="current item image">
                </div>
            <?php endif; ?>

            <!-- ── Upload new image (optional) ───────────────── -->
            <div class="form-group">
                <label class="form-label">Replace Photo (optional)</label>
                <div class="upload-section" onclick="document.getElementById('image').click();">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">Click to choose or drag-and-drop new image</div>
                    <div class="upload-hint">JPG, PNG, GIF, WEBP • Max 5 MB</div>
                    <input type="file" id="image" name="image" class="file-input" accept="image/*" onchange="previewImage(this)">
                </div>
                <div id="image-preview" class="image-preview" style="display:none;">
                    <img id="preview-img" class="preview-image" src="#" alt="preview">
                    <br><a href="#" class="remove-image" onclick="return removeImage();">Remove new image</a>
                </div>
            </div>

            <!-- ── Actions ───────────────────────────────────── -->
            <div class="form-actions">
                <a href="item_detail.php?id=<?= $item['id'] ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Item</button>
            </div>
        </form>
    </div>
</main>

<script>
function previewImage(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('image-preview').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}

function removeImage() {
    document.getElementById('image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    return false; // prevent link navigation
}

// simple drag-and-drop
const dropArea = document.querySelector('.upload-section');
['dragover','dragenter'].forEach(evt =>
    dropArea.addEventListener(evt, e => {
        e.preventDefault(); dropArea.classList.add('dragover');
    }));
['dragleave','drop'].forEach(evt =>
    dropArea.addEventListener(evt, e => {
        e.preventDefault(); dropArea.classList.remove('dragover');
    }));
dropArea.addEventListener('drop', e => {
    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('image').files = files;
        previewImage(document.getElementById('image'));
    }
});
</script>
</body>
</html>
