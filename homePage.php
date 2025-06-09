<?php 
require 'config.php';

// Pagination setup
$limit = 5; // Items per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch items
$stmt = $pdo->prepare("SELECT * FROM lost_found_items ORDER BY date_posted DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// Total count for pagination
$total = $pdo->query("SELECT COUNT(*) FROM lost_found_items")->fetchColumn();
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Lost & Found</title>
    <link rel="stylesheet" href="homePage.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Campus Lost & Found</h1>
            <div class="actions">
                <a href="post-item.php" class="btn">Post Found Item</a>
            </div>
        </header>

        <main>
            <section class="found-items">
                <h2>Found Items</h2>
                <p class="item-count"><?= count($items) ?> items found</p>
                
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <div class="item-header">
                            <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                        </div>
                        
                        <div class="item-body">
                            <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
                            
                            <div class="item-meta">
                                <span class="location"><?= htmlspecialchars($item['location_found']) ?></span>
                                <span class="date">Found on <?= date('M j, Y', strtotime($item['date_found'])) ?></span>
                                <span class="posted-by">Posted by <?= htmlspecialchars($item['poster_name']) ?></span>
                            </div>
                        </div>
                        
                        <div class="item-footer">
                            <a href="item-details.php?id=<?= $item['id'] ?>" class="view-details">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="page-link">Previous</a>
                    <?php endif; ?>
                    
                    <?php if ($page < $pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="page-link">Next</a>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>