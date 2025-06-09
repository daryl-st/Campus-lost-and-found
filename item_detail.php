<?php
session_start();
require 'includes/db.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: index.php");
    exit();
}

try {
    // Check if status column exists, if not add it
    $stmt = $pdo->query("SHOW COLUMNS FROM found_items LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        // Add status column if it doesn't exist
        $pdo->exec("ALTER TABLE found_items ADD COLUMN status ENUM('active', 'claimed', 'removed') DEFAULT 'active'");
        $pdo->exec("UPDATE found_items SET status = 'active' WHERE status IS NULL");
    }

    $stmt = $pdo->prepare("SELECT fi.*, u.username, u.email, u.phone FROM found_items fi
                           JOIN users u ON fi.user_id = u.id WHERE fi.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: index.php");
    exit();
}

// Check if current user owns this item
$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['title']) ?> | Campus Lost & Found</title>
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

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #4f46e5, #818cf8);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
            font-size: 0.9rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .btn-secondary:hover {
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .breadcrumb {
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .breadcrumb a {
            color: #4f46e5;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #3730a3;
        }

        .item-detail-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .item-header {
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .item-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .item-title {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .category-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .item-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px;
        }

        .item-image-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-image {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #94a3b8;
        }

        .placeholder-icon {
            font-size: 4rem;
            margin-bottom: 10px;
        }

        .item-info-section {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .info-group {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #4f46e5;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #1f2937;
            font-size: 1rem;
            line-height: 1.6;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .meta-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .meta-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: block;
        }

        .meta-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-weight: 600;
            color: #1e293b;
        }

        .contact-section {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .contact-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            text-align: center;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .contact-icon {
            font-size: 1.2rem;
            color: #4f46e5;
        }

        .contact-label {
            font-weight: 500;
            color: #374151;
            min-width: 80px;
        }

        .contact-value {
            color: #1f2937;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 12px 24px;
            font-size: 1rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #4f46e5;
            color: #4f46e5;
            box-shadow: none;
        }

        .btn-outline:hover {
            background: rgba(79, 70, 229, 0.1);
            box-shadow: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .btn-edit:hover {
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }

        .btn-delete:hover {
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-found {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
        }

        .btn-found:hover {
            box-shadow: 0 6px 15px rgba(245, 158, 11, 0.3);
        }

        .owner-actions {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .owner-actions h4 {
            color: #92400e;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .owner-actions p {
            color: #a16207;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .modal h3 {
            color: #1e293b;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .modal p {
            color: #64748b;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .close {
            color: #94a3b8;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            margin-top: -10px;
        }

        .close:hover {
            color: #64748b;
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

            .item-content {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 30px 20px;
            }

            .meta-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .item-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .item-header {
                padding: 20px;
            }

            .item-title {
                font-size: 1.5rem;
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="username">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="dashboard.php" class="btn">Dashboard</a>
                    <a href="logout.php" class="logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn btn-secondary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a> / <a href="index.php">Found Items</a> / <?= htmlspecialchars($item['title']) ?>
        </div>

        <div class="item-detail-card">
            <div class="item-header">
                <h1 class="item-title"><?= htmlspecialchars($item['title']) ?></h1>
                <span class="category-badge"><?= htmlspecialchars($item['category']) ?></span>
            </div>

            <div class="item-content">
                <div class="item-image-section">
                    <div class="main-image">
                        <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <?php else: ?>
                            <div class="placeholder-image">
                                <span class="placeholder-icon">📷</span>
                                <p>No image available</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="meta-grid">
                        <div class="meta-item">
                            <span class="meta-icon">📍</span>
                            <div class="meta-label">Found Location</div>
                            <div class="meta-value"><?= htmlspecialchars($item['location']) ?></div>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">📅</span>
                            <div class="meta-label">Date Found</div>
                            <div class="meta-value"><?= date('M j, Y', strtotime($item['found_datetime'])) ?></div>
                        </div>
                    </div>
                </div>

                <div class="item-info-section">
                    <div class="info-group">
                        <div class="info-label">Description</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($item['description'])) ?></div>
                    </div>

                    <div class="contact-section">
                        <h3 class="contact-title">Contact Information</h3>
                        <div class="contact-info">
                            <div class="contact-item">
                                <span class="contact-icon">👤</span>
                                <span class="contact-label">Name:</span>
                                <span class="contact-value"><?= htmlspecialchars($item['username']) ?></span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon">📧</span>
                                <span class="contact-label">Email:</span>
                                <span class="contact-value"><?= htmlspecialchars($item['email']) ?></span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon">📱</span>
                                <span class="contact-label">Phone:</span>
                                <span class="contact-value"><?= htmlspecialchars($item['phone']) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($isOwner): ?>
                    <div class="owner-actions">
                        <h4>🔧 Item Management</h4>
                        <p>You are the owner of this item. You can edit, mark as found, or delete this listing.</p>
                        <div class="action-buttons">
                            <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-edit btn-large">✏️ Edit Item</a>
                            <button onclick="markAsFound()" class="btn btn-found btn-large">✅ Mark as Found</button>
                            <button onclick="confirmDelete()" class="btn btn-delete btn-large">🗑️ Delete Item</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="action-buttons" style="margin-top: 30px;">
            <a href="index.php" class="btn btn-outline btn-large">← Back to Items</a>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Delete Item</h3>
            <p>Are you sure you want to delete this item? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button onclick="closeModal()" class="btn btn-outline">Cancel</button>
                <button onclick="deleteItem()" class="btn btn-delete">Delete</button>
            </div>
        </div>
    </div>

    <!-- Mark as Found Modal -->
    <div id="foundModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeFoundModal()">&times;</span>
            <h3>Mark as Found</h3>
            <p>Mark this item as found by its owner? This will remove it from the active listings.</p>
            <div class="modal-buttons">
                <button onclick="closeFoundModal()" class="btn btn-outline">Cancel</button>
                <button onclick="markItemAsFound()" class="btn btn-found">Mark as Found</button>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function markAsFound() {
            document.getElementById('foundModal').style.display = 'block';
        }

        function closeFoundModal() {
            document.getElementById('foundModal').style.display = 'none';
        }

        function deleteItem() {
            window.location.href = 'delete_item.php?id=<?= $item['id'] ?>';
        }

        function markItemAsFound() {
            window.location.href = 'mark_found.php?id=<?= $item['id'] ?>';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const deleteModal = document.getElementById('deleteModal');
            const foundModal = document.getElementById('foundModal');
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
            if (event.target == foundModal) {
                foundModal.style.display = 'none';
            }
        }

        // Debug: Log ownership status
        console.log('Current user ID: <?= $_SESSION['user_id'] ?? 'not logged in' ?>');
        console.log('Item owner ID: <?= $item['user_id'] ?>');
        console.log('Is owner: <?= $isOwner ? 'true' : 'false' ?>');
    </script>
</body>
</html>
