<?php
require 'includes/db.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT fi.*, u.username, u.email, u.phone FROM found_items fi
                       JOIN users u ON fi.user_id = u.id WHERE fi.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) die("Item not found");
?>

<h2><?= htmlspecialchars($item['title']) ?></h2>
<img src="<?= $item['image_path'] ?>" width="300">
<p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
<p><strong>Found at:</strong> <?= htmlspecialchars($item['location']) ?></p>
<p><strong>Found on:</strong> <?= htmlspecialchars($item['found_datetime']) ?></p>
<p><strong>Description:</strong><br> <?= nl2br(htmlspecialchars($item['description'])) ?></p>

<h3>Found By:</h3>
<p><strong>Name:</strong> <?= htmlspecialchars($item['username']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($item['email']) ?></p>
<p><strong>Phone:</strong> <?= htmlspecialchars($item['phone']) ?></p>
