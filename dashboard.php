<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'includes/db.php';

// Get success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch user's posted items (only active ones)
$stmt = $pdo->prepare("SELECT * FROM found_items WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$userItems = $stmt->fetchAll();

// Fetch recent items (not posted by the current user, only active ones)
$stmt = $pdo->prepare("SELECT * FROM found_items WHERE user_id != ? AND status = 'active' ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recentItems = $stmt->fetchAll();

// Get stats
$totalItems = count($userItems);

// Get claimed items count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM found_items WHERE user_id = ? AND status = 'claimed'");
$stmt->execute([$_SESSION['user_id']]);
$claimedItems = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Campus Lost & Found</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">🔍</span> Campus Lost & Found
            </a>
            <div class="actions">
                <span class="username">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="post_item.php" class="btn btn-small">Post Found Item</a>
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if ($success_message): ?>
            <div class="success-alert">
                <p><?= htmlspecialchars($success_message) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-alert">
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php endif; ?>

        <div class="dashboard-welcome">
            <div class="welcome-content">
                <h1>Welcome to Your Dashboard</h1>
                <p>Manage your found items and help reconnect people with their belongings</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <section class="stats-section card">
                <h2 class="section-title">Your Activity</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-value"><?= $totalItems ?></div>
                        <div class="stat-label">Active Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value"><?= $claimedItems ?></div>
                        <div class="stat-label">Items Claimed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🏆</div>
                        <div class="stat-value"><?= ($totalItems + $claimedItems) > 0 ? '👍' : '-' ?></div>
                        <div class="stat-label">Community Helper</div>
                    </div>
                </div>
            </section>

            <section class="quick-actions card">
                <h2 class="section-title">Quick Actions</h2>
                <div class="action-buttons">
                    <a href="post_item.php" class="action-btn">
                        <span class="action-icon">➕</span>
                        <span class="action-text">Post New Item</span>
                    </a>
                    <a href="index.php" class="action-btn">
                        <span class="action-icon">🔍</span>
                        <span class="action-text">Browse Items</span>
                    </a>
                   
                </div>
            </section>

            <section class="your-items-section card">
                <div class="section-header">
                    <h2 class="section-title">Your Posted Items</h2>
                    <a href="post_item.php" class="btn btn-small">+ Post New</a>
                </div>
                
                <?php if (count($userItems) > 0): ?>
                <div class="items-list">
                    <?php foreach ($userItems as $index => $item): ?>
                    <div class="item-row animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s">
                        <div class="item-info">
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                            <div class="item-meta">
                                <span class="location-tag">
                                    <span class="meta-icon">📍</span>
                                    <?= htmlspecialchars($item['location']) ?>
                                </span>
                                <span class="date-tag">
                                    <span class="meta-icon">📅</span>
                                    <?= date('M j, Y', strtotime($item['found_datetime'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="item-actions">
                            <a href="item_detail.php?id=<?= $item['id'] ?>" class="item-action view">View</a>
                            <a href="edit_item.php?id=<?= $item['id'] ?>" class="item-action edit">Edit</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h3>No Items Yet</h3>
                    <p>You haven't posted any found items yet</p>
                    <a href="post_item.php" class="btn mt-3">Post Your First Item</a>
                </div>
                <?php endif; ?>
            </section>

         
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="logo-icon">🔍</span> Campus Lost & Found
                </div>
                <p class="footer-tagline">Helping our campus community reconnect with lost items</p>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
                    <a href="privacy.php">Privacy Policy</a>
                </div>
                <p class="copyright">© <?= date('Y') ?> Campus Lost & Found. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
        .success-alert {
            background: #c6f6d5;
            color: #2f855a;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            border-left: 4px solid #38a169;
        }

        .error-alert {
            background: #fed7d7;
            color: #c53030;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            border-left: 4px solid #e53e3e;
        }
    </style>
</body>
</html>
