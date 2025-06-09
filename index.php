<?php
require 'includes/db.php';

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 6; // Increased for better grid display
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Prepare SQL with search filter
$sql = "SELECT * FROM found_items";
if (!empty($search)) {
    $sql .= " WHERE title LIKE :search OR description LIKE :search";
}
$sql .= " ORDER BY created_at DESC LIMIT :start, :limit";

$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM found_items";
if (!empty($search)) {
    $countSql .= " WHERE title LIKE :search OR description LIKE :search";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $countStmt->execute();
} else {
    $countStmt = $pdo->query($countSql);
}
$total = $countStmt->fetchColumn();
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Lost & Found</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="dashboard.php" class="btn btn-small">Dashboard</a>
                    <a href="logout.php" class="logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-small">Login</a>
                    <a href="register.php" class="btn btn-secondary btn-small">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Find What You've Lost</h1>
                <p>Our campus community helps reconnect people with their lost belongings</p>
                
                <form method="get" action="" class="search-form">
                    <div class="search-container">
                        <input type="text" name="search" placeholder="Search for lost items..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="search-btn">
                            <span class="search-icon">🔍</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="section found-items-section">
            <div class="section-header">
                <h2 class="section-title">Found Items</h2>
                <p class="item-count"><?= $total ?> items found</p>
            </div>

            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <h3>No items found</h3>
                    <p>Try adjusting your search or check back later</p>
                </div>
            <?php else: ?>
                <div class="items-grid">
                    <?php foreach ($items as $index => $item): ?>
                        <div class="item-card card animate-fade-in" style="animation-delay: <?= $index * 0.1 ?>s">
                            <div class="item-image">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <span class="placeholder-icon">📷</span>
                                    </div>
                                <?php endif; ?>
                                <span class="category-badge"><?= htmlspecialchars($item['category']) ?></span>
                            </div>
                            <div class="item-content">
                                <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                                <p class="item-description"><?= htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : '') ?></p>
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
                                <div class="item-footer">
                                    <a href="item_detail.php?id=<?= $item['id'] ?>" class="view-details-btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pagination">
                    <?php if ($pages > 1): ?>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-link">
                                <span class="page-arrow">←</span> Previous
                            </a>
                        <?php endif; ?>
                        
                        <div class="page-numbers">
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($pages, $start_page + 4);
                            
                            if ($start_page > 1): ?>
                                <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-number">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span class="page-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                                   class="page-number <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $pages): ?>
                                <?php if ($end_page < $pages - 1): ?>
                                    <span class="page-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="?page=<?= $pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-number"><?= $pages ?></a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($page < $pages): ?>
                            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-link">
                                Next <span class="page-arrow">→</span>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

 <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="logo-icon">🔍</span> Campus Lost & Found
                </div>
                <p class="footer-tagline">Helping our campus community reconnect with lost items</p>
                <p class="copyright">© <?= date('Y') ?> Campus Lost & Found. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
